<?php
namespace Upyun;

class Upyun {

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config) {
        $this->setConfig($config);
    }

    public function setConfig(Config $config) {
        $this->config = $config;
        return $this;
    }

    /**
     * 上传文件
     * @param string $path: 文件保存在 UPYUN 服务的路径
     * @param string|resource $content: 文件内容或文件句柄
     * @param array $params: 自定义参数
     *
     * @return array: 若文件是图片则返回 x-upyun-width 等信息，否则为空数组
     * @throws \Exception
     */
    public function write($path, $content, $params = array()) {
        if(is_resource($content)) {
            $stat = fstat($content);
            $size = $stat['size'];
        } else {
            $size = strlen($content);
        }
        $authHeader = Signature::getRestApiSignHeader($this->config, 'PUT', $path, $size);

        $response = Request::put(
            $this->config->getRestApiUrl($path),
            array_merge($authHeader,  $params),
            $content
        );
        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }
        return Util::getHeaderParams($response->header);
    }

    /**
     * 读取文件内容
     * @param $path
     * @param array $params: 额外的 HTTP 头参数
     *
     * @return mixed: 读取文件时返回文件内容或 TRUE；读取目录时返回一个包含文件列表的数组
     * @throws \Exception
     */
    public function read($path, $resource = NULL, $params = array()) {
        $authHeader = Signature::getRestApiSignHeader($this->config, 'GET', $path, 0);

        $response = Request::get(
            $this->config->getRestApiUrl($path),
            array_merge($authHeader, $params),
            $resource
        );

        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }

        $params = Util::getHeaderParams($response->header);
        if(! isset($params['x-upyun-list-iter'])) {
            return $response->body;
        } else {
            $files = array();
            if(!$response->body) {
                return array('files' => $files, 'is_end' => true);
            }

            $lines = explode("\n", $response->body);
            foreach($lines as $line) {
                $file = array();
                list($file['name'], $file['type'], $file['size'], $file['time']) = explode("\t", $line, 4);
                array_push($files, $file);
            }

            return array('files' => $files, 'is_end' => $params['x-upyun-list-iter'] === 'g2gCZAAEbmV4dGQAA2VvZg');
        }
    }

    /**
     * 判断文件是否存在
     * @param $path
     *
     * @return bool
     * @throws \Exception
     */
    public function has($path) {
        $authHeader = Signature::getRestApiSignHeader($this->config, 'HEAD', $path, 0);

        $response = Request::head(
            $this->config->getRestApiUrl($path),
            $authHeader
        );
        if($response->status_code === 200) {
            return true;
        } else if($response->status_code === 404) {
           return false;
        } else {
            throw new \Exception('head request failed, with status code: ' . $response->status_code);
        }
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function info($path) {
        $header = Signature::getRestApiSignHeader($this->config, 'HEAD', $path, 0);

        $response = Request::head(
            $this->config->getRestApiUrl($path),
            $header
        );
        return Util::getHeaderParams($response->header);
    }

    /**
     * 删除文件或者目录
     * @param $path
     *
     * @return mixed
     * @throws \Exception: 删除不存在的文件将会抛出异常
     */
    public function delete($path) {
        $authHeader = Signature::getRestApiSignHeader($this->config, 'DELETE', $path, 0);

        $reponse = Request::delete(
            $this->config->getRestApiUrl($path),
            $authHeader
        );
        if($reponse->status_code !== 200) {
            $body = json_decode($reponse->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }
        return $reponse->body;
    }

    /**
     * 创建目录
     * @param $path
     *
     * @throws \Exception
     */
    public function createDir($path) {
        $path = rtrim($path, '/') . '/';
        $authHeader = Signature::getRestApiSignHeader($this->config, 'PUT', $path, 0);

        $response = Request::put(
            $this->config->getRestApiUrl($path),
            array_merge($authHeader, array('folder' => 'true'))
        );
        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }
    }

    /**
     * 删除文件或者目录
     * @param $path
     *
     * @return mixed
     * @throws \Exception
     */
    public function deleteDir($path) {
        return $this->delete($path);
    }

    /**
     * @return string: 空间大小，单位字节
     * @throws \Exception
     */
    public function usage() {
        $header = Signature::getRestApiSignHeader($this->config, 'GET', '/', 0);

        $response = Request::get(
            $this->config->getRestApiUrl('/?usage'),
            $header
        );
        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }
        return $response->body;
    }

    /**
     * 刷新资源缓存
     * @param array $urls: 需要刷新的 url 列表
     *
     * @return array: 刷新失败的 url 列表，若全部刷新成功则为空数组
     */
    public function purge($urls) {
        $urlString = $urls;
        if(is_array($urls)) {
            $urlString = implode("\n", $urls);
        }

        $headers = Signature::getPurgeSignHeader($this->config, $urlString);
        $response = Request::post(
            Config::ED_PURGE,
            $headers,
            array('purge' => $urlString)
        );
        $result = json_decode($response->body, true);
        return $result['invalid_domain_of_url'];
    }
}
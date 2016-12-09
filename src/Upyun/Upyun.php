<?php
namespace Upyun;

use Upyun\Api\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp;

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
        if(!$content) {
            throw new \Exception('write content can not be empty.');
        }

        $upload = new Uploader($this->config);
        return $upload->upload($path, $content, $params);
        return Util::getHeaderParams($response->getHeaders());
    }

    /**
     * 读取文件内容
     * @param $path
     * @param array $params: 额外的 HTTP 头参数
     *
     * @return mixed: 读取文件时返回文件内容或 TRUE；读取目录时返回一个包含文件列表的数组
     * @throws \Exception
     */
    public function read($path, $saveHandler = NULL, $params = array()) {
        $req = new Rest($this->config);
        $response = $req->request('GET', $path)
            ->withHeaders($params)
            ->send();


        $params = Util::getHeaderParams($response->getHeaders());


        if(! isset($params['x-upyun-list-iter'])) {
            if(is_resource($saveHandler)) {
                Psr7\copy_to_stream($response->getBody(), Psr7\stream_for($saveHandler));
                return true;
            } else {
                return $response->getBody()->getContents();
            }
        } else {
            $files = Util::parseDir($response->getBody());
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
        $req = new Rest($this->config);
        try {
            $response = $req->request('HEAD', $path)
                            ->send();
        } catch(GuzzleHttp\Exception\BadResponseException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            if($statusCode === 404) {
                return false;
            } else {
                throw $e;
            }
        }

        return true;
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function info($path) {
        $req = new Rest($this->config);
        $response = $req->request('HEAD', $path)
                        ->send();
        return Util::getHeaderParams($response->getHeaders());
    }

    /**
     * 删除文件或者目录
     * @param $path
     * @param $async
     *
     * @return mixed
     * @throws \Exception: 删除不存在的文件将会抛出异常
     */
    public function delete($path, $async = false) {
        $req = new Rest($this->config);
        $req->request('DELETE', $path);
        if($async) {
            $req->withHeader('x-upyun-async', 'true');
        }
        $req->send();
    }

    /**
     * 创建目录
     * @param $path
     *
     * @throws \Exception
     */
    public function createDir($path) {
        $path = rtrim($path, '/') . '/';
        $req = new Rest($this->config);
        $req->request('POST', $path)
            ->withHeader('folder', 'true')
            ->send();
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

        $req = new Rest($this->config);
        $response = $req->request('GET', '/?usage')
            ->withHeader('folder', 'true')
            ->send();

        return $response->getBody()->getContents();
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

        $client = new Client([
            'timeout' => $this->config->timeout
        ]);
        $response = $client->request('POST', Config::ED_PURGE, [
            'headers' =>  Signature::getPurgeSignHeader($this->config, $urlString),
            'form_params' => ['purge' => $urlString]
        ]);
        $result = json_decode($response->getBody()->getContents(), true);
        return $result['invalid_domain_of_url'];
    }
}
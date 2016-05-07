<?php
namespace Upyun;

/**
 * Class BucketConfig
 * 
 * @package Upyun
 */
class BucketConfig {

    /**
     * @var string: 服务名
     */
    public $bucketName;
    /**
     * @var string: 操作员名称
     */
    public $operatorName;
    /**
     * @var string: 操作员密码
     */
    public $operatorPassword;
    /**
     * @var string: 表单 API 秘钥，通过管理后台获取
     */
    public $formApiKey;

    /**
     * @var string: HTTP REST API 和 HTTP FORM  API 所使用的接口地址, 默认 ED_AUTO
     */
    protected $restApiEndPoint;
    
    /**
     * 适合不同国内不同线路的接口地址
     * 关于国内不同线路选择的详细描述见: http://docs.upyun.com/api/
     */
    const ED_AUTO            = 'v0.api.upyun.com';
    const ED_TELECOM         = 'v1.api.upyun.com';
    const ED_CNC             = 'v2.api.upyun.com';
    const ED_CTT             = 'v3.api.upyun.com';

    /**
     * 分块上传接口地址
     */
    const ED_FORM            = 'm0.api.upyun.com';

    /**
     * 视频预处理接口地址
     */
    const ED_VIDEO           = 'p0.api.upyun.com';

    /**
     * 单个 URL 刷新接口地址
     */
    const ED_PURGE           = 'http://purge.upyun.com/purge/';

    /**
     * 获取分块上传接口的签名
     */
    const SIGN_MULTIPART     = 1;
    /**
     * 生成视频处理接口的签名
     */
    const SIGN_VIDEO         = 2;
    /**
     * 生成视频处理接口的签名（不需要操作员时使用）
     */
    const SIGN_VIDEO_NO_OPERATOR   = 3;


    public function __construct($bucketName, $operatorName, $operatorPassword) {
        $this->bucketName = $bucketName;
        $this->operatorName = $operatorName;
        $this->operatorPassword = $operatorPassword;
        $this->restApiEndPoint = self::ED_AUTO;
    }

    public function setRestApiEndPoint($restApiEndPoint) {
        $this->restApiEndPoint = $restApiEndPoint;
    }

    /**
     * 生成 REST API 签名
     * @param $method
     * @param $path
     * @param $date
     * @param $contentLength
     *
     * @return mixed
     */
    public function generateRestApiSignature($method, $path, $date, $contentLength) {
        $path = '/' . $this->bucketName . '/' . ltrim($path, '/');
        $md5Pwd = md5($this->operatorPassword);
        return md5("$method&$path&$date&$contentLength&$md5Pwd");
    }

    /**
     * 获取 RESET API 请求需要的签名头
     * @param $method
     * @param $path
     * @param $contentLength
     *
     * @return array
     */
    public function getRestApiSignHeader($method, $path, $contentLength) {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $sign = $this->generateRestApiSignature($method, $path, $date, $contentLength);
        $header = $this->getSignHeader($sign);
        $header['Date'] = $date;
        return $header;
    }

    /**
     * 获取请求缓存刷新接口需要的签名头
     * @param $urlString
     *
     * @return array
     */
    public function getPurgeSignHeader($urlString) {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $md5Pwd = md5($this->operatorPassword);
        $sign = md5("$urlString&{$this->bucketName}&$date&$md5Pwd");
        return array(
            'Authorization' => "UpYun {$this->bucketName}:{$this->operatorName}:$sign",
            'Date' => $date,
        );
    }

    public function getSignHeader($sign) {
        return array('Authorization' => "UpYun {$this->operatorName}:$sign");
    }

    /**
     * 根据文件路径,获取接口地址
     * @param $remoteFilePath
     *
     * @return string
     */
    public function getRestApiUrl($remoteFilePath) {
        return "http://{$this->restApiEndPoint}/{$this->bucketName}/" . ltrim($remoteFilePath, '/');
    }
    
    public function getRestApiEntryPoint() {
        return "http://{$this->restApiEndPoint}/{$this->bucketName}/";
    }

    public function getPolicy($requestParams) {
        return $this->base64Json($requestParams);
    }

    public function base64Json($params) {
        return base64_encode(json_encode($params));
    }

    public function getSignature($data, $type, $tokenSecret = '') {
        if(is_array($data)) {
            ksort($data);
            $string = '';
            foreach($data as $k => $v) {
                if(is_array($v)) {
                    $v = implode('', $v);
                }
                $string .= "$k$v";
            }
            switch($type) {
                case self::SIGN_MULTIPART:
                    $string .= $tokenSecret ? $tokenSecret : $this->formApiKey;
                    break;
                case self::SIGN_VIDEO:
                    $string = $this->operatorName . $string . md5($this->operatorPassword);
                    break;
                case self::SIGN_VIDEO_NO_OPERATOR:
                    break;

            }
            $sign = md5($string);
            return $sign;
        }
        return false;
    }
}
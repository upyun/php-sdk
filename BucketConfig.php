<?php
namespace Upyun;

/**
 * Class BucketConfig
 * @package Upyun
 * 服务配置信息
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
     * HTTP REST API 和 HTTP FORM  API 所使用的线路
     */
    const ED_AUTO            = 'v0.api.upyun.com';
    const ED_TELECOM         = 'v1.api.upyun.com';
    const ED_CNC             = 'v2.api.upyun.com';
    const ED_CTT             = 'v3.api.upyun.com';

    /**
     * 分块上传 API 线路
     */
    const ED_FORM            = 'm0.api.upyun.com';

    /**
     * 视频预处理接口使用的线路
     */
    const ED_VIDEO           = 'p0.api.upyun.com';

    /**
     * 刷新接口
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

    protected $restApiEndPoint;

    public function __construct($bucketName, $operatorName, $operatorPassword) {
        $this->bucketName = $bucketName;
        $this->operatorName = $operatorName;
        $this->operatorPassword = $operatorPassword;
        $this->restApiEndPoint = self::ED_AUTO;
    }

    public function setRestApiEndPoint($restApiEndPoint) {
        $this->restApiEndPoint = $restApiEndPoint;
    }

    public function getRestApiSign($method, $path, $date, $contentLength) {
        $path = '/' . $this->bucketName . '/' . ltrim($path, '/');
        $md5Pwd = md5($this->operatorPassword);
        return md5("$method&$path&$date&$contentLength&$md5Pwd");
    }

    public function getRestApiSignHeader($method, $path, $contentLength) {
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $sign = $this->getRestApiSign($method, $path, $date, $contentLength);
        $header = $this->getSignHeader($sign);
        $header['Date'] = $date;
        return $header;
    }

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

    public function getRestApiUrl($path) {
        return "http://{$this->restApiEndPoint}/{$this->bucketName}/" . ltrim($path, '/');
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
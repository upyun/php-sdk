<?php
namespace Upyun;


/**
 * Class Signature
 * @package Upyun
 */
class Signature {
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

    /**
     * 获取 RESET API 请求需要的签名头
     * 
     * @param Config $bucketConfig
     * @param $method
     * @param $path
     * @param $contentLength
     *
     * @return array
     */
    public static function getRestApiSignHeader($bucketConfig, $method, $remotePath, $contentLength) {
        $gmtDate = gmdate('D, d M Y H:i:s \G\M\T');
        $path = Util::pathJoin($bucketConfig->bucketName, $remotePath);
        $sign = md5("$method&$path&$gmtDate&$contentLength&{$bucketConfig->getOperatorPassword()}");
        
        $headers = array(
            'Authorization' => "UpYun {$bucketConfig->operatorName}:$sign",
            'Date' => $gmtDate
        );
        return $headers;
    }

    /**
     * 获取请求缓存刷新接口需要的签名头
     * 
     * @param Config $bucketConfig
     * @param $urlString
     *
     * @return array
     */
    public static function getPurgeSignHeader( Config $bucketConfig, $urlString) {
        $gmtDate = gmdate('D, d M Y H:i:s \G\M\T');
        $sign = md5("$urlString&{$bucketConfig->bucketName}&$gmtDate&{$bucketConfig->getOperatorPassword()}");
        return array(
            'Authorization' => "UpYun {$bucketConfig->bucketName}:{$bucketConfig->operatorName}:$sign",
            'Date' => $gmtDate,
        );
    }

    public static function getSignature( Config $bucketConfig, $data, $type, $tokenSecret = '') {
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
                    $string .= $tokenSecret ? $tokenSecret : $bucketConfig->getFormApiKey();
                    break;
                case self::SIGN_VIDEO:
                    $string = $bucketConfig->operatorName . $string . $bucketConfig->getOperatorPassword();
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
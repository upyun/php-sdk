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
     * 获取 Header 签名需要的请求头
     *
     * @param Config $bucketConfig
     * @param $method 请求方法
     * @param $path  请求路径
     * @param $contentMd5 文件内容 md5
     *
     * @return array
     */
    public static function getHeaderSign($bucketConfig, $method, $path, $contentMd5 = null) {
        $gmtDate = gmdate('D, d M Y H:i:s \G\M\T');

        $policy = null;
        $sign = self::getBodySignature($bucketConfig, $method, $path, $gmtDate, $policy, $contentMd5);

        $headers = array(
            'Authorization' => $sign,
            'Date' => $gmtDate,
            'User-agent' => 'Php-Sdk/' . $bucketConfig->getVersion()
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
    public static function getPurgeSignHeader(Config $bucketConfig, $urlString) {
        $gmtDate = gmdate('D, d M Y H:i:s \G\M\T');
        $sign = md5("$urlString&{$bucketConfig->bucketName}&$gmtDate&{$bucketConfig->operatorPassword}");
        return array(
            'Authorization' => "UpYun {$bucketConfig->bucketName}:{$bucketConfig->operatorName}:$sign",
            'Date' => $gmtDate,
            'User-agent' => 'Php-Sdk/' . $bucketConfig->getVersion() . ' (purge api)'
        );
    }

    /**
     * 获取表单 API 需要的签名，依据 body 签名规则计算
     * @param Config $bucketConfig
     * @param $method 请求方法
     * @param $uri 请求路径
     * @param $date 请求时间
     * @param $policy
     * @param $contentMd5 请求 body 的 md5
     *
     * @return array
     */
    public static function getBodySignature(Config $bucketConfig, $method, $uri, $date = null, $policy = null, $contentMd5 = null) {
        $data = array(
            $method,
            $uri
        );
        if ($date) {
            $data[] = $date;
        }

        if ($policy) {
            $data[] = $policy;
        }

        if ($contentMd5) {
            $data[] = $contentMd5;
        }
        $signature = base64_encode(hash_hmac('sha1', implode('&', $data), $bucketConfig->operatorPassword, true));
        return 'UPYUN ' . $bucketConfig->operatorName . ':' . $signature;
    }

    public static function getSignature(Config $bucketConfig, $data, $type, $tokenSecret = '') {
        if (is_array($data)) {
            ksort($data);
            $string = '';
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $v = implode('', $v);
                }
                $string .= "$k$v";
            }
            switch ($type) {
                case self::SIGN_MULTIPART:
                    $string .= $tokenSecret ? $tokenSecret : $bucketConfig->getFormApiKey();
                    break;
                case self::SIGN_VIDEO:
                    $string = $bucketConfig->operatorName . $string . $bucketConfig->operatorPassword;
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

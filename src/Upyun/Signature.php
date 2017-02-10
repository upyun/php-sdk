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
     * 获取 Header 签名
     *
     * @param Config $bucketConfig
     * @param $method
     * @param $path  请求路径
     * @param $contentMd5 文件内容 md5
     *
     * @return array
     */
    public static function getHeaderSign($bucketConfig, $method, $path, $contentMd5 = null) {
        $gmtDate = gmdate('D, d M Y H:i:s \G\M\T');

        $signParams = array(
            $method,
            $path,
            $gmtDate
        );

        if ($contentMd5) {
            $signParams[] = $contentMd5;
        }

        $sign = self::calcSignature($bucketConfig, $signParams);

        $headers = array(
            'Authorization' => "UPYUN {$bucketConfig->operatorName}:$sign",
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
     * @param $data
     *
     * @return array
     */
    public static function getFormSignature(Config $bucketConfig, $data) {
        $data['bucket'] = $bucketConfig->bucketName;
        $policy = Util::base64Json($data);
        $signParams = array(
            'method' => 'POST',
            'uri' => '/' . $bucketConfig->bucketName,
        );
        if (isset($data['date'])) {
            $signParams['date'] = $data['date'];
        }

        $signParams['policy'] = $policy;
        if (isset($data['content-md5'])) {
            $signParams['md5'] = $data['content-md5'];
        };

        $signature = self::calcSignature($bucketConfig, $signParams);
        return array(
            'policy' => $policy,
            'signature' => $signature
        );
    }

    private static function calcSignature(Config $bucketConfig, $signParams) {
        return base64_encode(hash_hmac('sha1', implode('&', $signParams), $bucketConfig->operatorPassword, true));
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

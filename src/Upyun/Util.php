<?php
namespace Upyun;
class Util {

    public static function trim($str) {
        if(is_array($str)) {
            return array_map(array('Util', 'trim'), $str);
        } else {
            return trim($str);
        }
    }

    //todo remove this method
    public static function multiPartPost($postData, $url, $retryTimes = 3) {
        $delimiter = '-------------' . uniqid();
        $data = '';
        foreach($postData as $name => $content) {
            if(is_array($content)) {
                //上传文件
                $data .= "--" . $delimiter . "\r\n";
                $filename = isset($content['name']) ? $content['name'] : $name;
                $data .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $filename . "\" \r\n";
                $type = isset($content['type']) ? $content['type'] : 'application/octet-stream';
                $data .= 'Content-Type: ' . $type . "\r\n\r\n";
                $data .= $content['data'] . "\r\n";
            } else {
                //普通参数
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"';
                $data .= "\r\n\r\n" . $content . "\r\n";
            }
        }
        $data .= "--" . $delimiter . "--";

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER , array(
                'Content-Type: multipart/form-data; boundary=' . $delimiter,
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);

        $times = 0;
        do{
            $result = curl_exec($handle);
            $times++;
        } while($result === false && $times < $retryTimes);

        curl_close($handle);
        return $result;
    }

    public static function getHeaderParams($header) {
        preg_match_all('~(x-upyun-[a-z\-]*): ([0-9a-zA-Z]{0,32})~i', $header, $result, PREG_SET_ORDER);
        $meta = array();
        foreach($result as $value) {
            $meta[$value[1]] = $value[2];
        }
        return $meta;
    }

    public static function pathJoin() {
        $paths = func_get_args();
        foreach($paths as &$path) {
            $path = trim($path, '/');
        }
        return '/' . implode('/', $paths);
    }
    
    public static function base64Json($params) {
        return base64_encode(json_encode($params));
    }

    public static function stringifyHeaders($headers) {
        $return = array();
        foreach ($headers as $key => $value) {
            $return[] = "$key: $value";
        }
        return $return;
    }
}
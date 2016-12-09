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

    public static function getHeaderParams($headers) {
        $params = [];
        foreach ($headers as $header => $value) {
            if(strpos($header, 'x-upyun-') !== false) {
                $params[$header] = $value[0];
            }
        }
        return $params;
    }
    
    public static function parseDir($body) {
        $files = array();
        if(!$body) {
            return array('files' => $files, 'is_end' => true);
        }

        $lines = explode("\n", $body);
        foreach($lines as $line) {
            $file = [];
            list($file['name'], $file['type'], $file['size'], $file['time']) = explode("\t", $line, 4);
            $files[] = $file;
        }

        return $files;
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
    
    public static function md5Hash($resource) {
        rewind($resource);
        $ctx = hash_init('md5');
        hash_update_stream($ctx, $resource);
        $md5 = hash_final($ctx);
        return $md5;
    }
}
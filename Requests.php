<?php
namespace Upyun;

class Requests {

    public static function put($url, $headers = array(), $data = array()) {
        $curlOptions = array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
        );

        if(is_string($data)) {
            $curlOptions[CURLOPT_POSTFIELDS] = $data;
            $headers['Content-Length'] = strlen($data);
        } else if(is_resource($data)) {
            $curlOptions[CURLOPT_PUT] = true;
            $curlOptions[CURLOPT_INFILE] = $data;
            $curlOptions[CURLOPT_INFILESIZE] = fstat($data)['size'];
            $headers['Content-Length'] = $curlOptions[CURLOPT_INFILESIZE];
        } else if(is_array($data) && !empty($data)) {
            $json = json_encode($data);
            $headers['Content-Type'] = 'application/json';
            $headers['Content-Length'] = strlen($json);
            $curlOptions[CURLOPT_POSTFIELDS] = $json;
        }
        $curlOptions[CURLOPT_HTTPHEADER] = self::stringifyHeaders($headers);
        return self::request($url, $curlOptions);
    }

    public static function post($url, $headers = array(), $data = array()) {
        $curlOptions = array(
            CURLOPT_POST => true,
        );

        if(is_string($data)) {
            $curlOptions[CURLOPT_POSTFIELDS] = $data;
            $headers['Content-Length'] = strlen($data);
        } else if(is_array($data) && !empty($data)) {
            $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($data);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $headers['Content-Length'] = strlen($curlOptions[CURLOPT_POSTFIELDS]);
        }
        $curlOptions[CURLOPT_HTTPHEADER] = self::stringifyHeaders($headers);
        return self::request($url, $curlOptions);
    }

    public static function head($url, $headers = array(), $data = array()) {
        $curlOptions = array(
            CURLOPT_CUSTOMREQUEST => 'HEAD',
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => self::stringifyHeaders($headers)
        );
        $queryString = '';
        if(!empty($data)) {
            $queryString = '?' . http_build_query($data);
        }
        return self::request($url . $queryString, $curlOptions);
    }

    public static function get($url, $headers = array(), $data = array()) {
        $curlOptions = array(
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => self::stringifyHeaders($headers)
        );

        $queryString = '';
        if(is_array($data) && !empty($data)) {
            $queryString = '?' . http_build_query($data);
        } else if(is_resource($data)){
            $curlOptions[CURLOPT_HEADER] = false;
            $curlOptions[CURLOPT_RETURNTRANSFER] = 1;
            $curlOptions[CURLOPT_FILE] = $data;
        }
        return self::request($url . $queryString, $curlOptions);
    }

    public static function delete($url, $headers = array(), $data = array()) {
        $curlOptions = array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => self::stringifyHeaders($headers)
        );
        $queryString = '';
        if(!empty($data)) {
            $queryString = '?' . http_build_query($data);
        }
        return self::request($url . $queryString, $curlOptions);
    }

    private static function stringifyHeaders($headers) {
        $return = array();
        foreach ($headers as $key => $value) {
            $return[] = "$key: $value";
        }
        return $return;
    }

    private static function request($url, $curlOptions) {
        $ch = curl_init($url);
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        if(!isset($curlOptions[CURLOPT_HEADER])) {
            $curlOptions[CURLOPT_HEADER] = true;
        }

        curl_setopt_array($ch, $curlOptions);
        $result = curl_exec($ch);

        if($result === false) {
            $errorMsg = curl_error($ch);
            $errorNo = curl_errno($ch);
            curl_close($ch);
            throw new \Exception('curl error: ' . $errorMsg, $errorNo);
        }
        $response = array();
        if($curlOptions[CURLOPT_HEADER]) {
            list($response['header'], $response['body']) = explode("\r\n\r\n", $result, 2);
        } else {
            $response['header'] = null;
            $response['body'] = $result;
        }
        $response['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return (object)$response;
    }
}
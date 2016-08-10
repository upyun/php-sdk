<?php
namespace Upyun\Http;

use Upyun\Util;
use Upyun\Http\Response;
class Curl {

    public function exec($request) {

        $url = ($request->config->useSsl ? 'https://' : 'http://') . $request->uri;

        $ch = curl_init($url);
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_HTTPHEADER => Util::stringifyHeaders($request->headers),
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
        ];

        if(!empty($request->files)) {
            $options[CURLOPT_INFILE] = $request->files[0];
            $stat = fstat($request->files[0]);
            $options[CURLOPT_INFILESIZE] = $stat['size'];
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $response = Response::parseCurl($response);
        //TODO error

        curl_close($ch);
        return $response;
    }
}
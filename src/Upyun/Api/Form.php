<?php

namespace Upyun\Api;

use Upyun\Signature;
use Upyun\Util;
use GuzzleHttp\Client;

class Form extends Rest{

    public function upload($path, $stream, $params) {
        $params['save-key'] = $path;
        $params['bucket'] = $this->config->bucketName;
        if (!isset($params['expiration'])) {
            $params['expiration'] = time() + 30 * 60 * 60; // 30 分钟
        }

        $policy = Util::base64Json($params);
        $method = 'POST';
        $signature = Signature::getBodySignature($this->config, $method, '/' . $params['bucket'], null, $policy);
        $client = new Client([
            'timeout' => $this->config->timeout,
        ]);

        $url = ($this->config->useSsl ? 'https://' : 'http://') . $this->endpoint;

        $response = $client->request($method, $url, array(
            'multipart' => array(
                array(
                    'name' => 'policy',
                    'contents' => $policy,
                ),
                array(
                    'name' => 'authorization',
                    'contents' => $signature,
                ),
                array(
                    'name' => 'file',
                    'contents' => $stream,
                )
            )
        ));
        return $response->getStatusCode() === 200;
    }
}

<?php

namespace Upyun\Api;

use Upyun\Signature;
use GuzzleHttp\Client;

class Form extends Rest{

    public function upload($path, $stream, $params) {
        $params['save-key'] = $path;
        $params['bucket'] = $this->config->bucketName;
        if (!isset($params['expiration'])) {
            $params['expiration'] = time() + 30 * 60 * 60; // 30 分钟
        }

        $result = Signature::getFormSignature($this->config, $params);
        $policy = $result['policy'];
        $signature = $result['signature'];
        $client = new Client([
            'timeout' => $this->config->timeout,
        ]);

        $url = ($this->config->useSsl ? 'https://' : 'http://') . $this->endpoint;

        $response = $client->request('POST', $url, array(
            'multipart' => array(
                array(
                    'name' => 'policy',
                    'contents' => $policy,
                ),
                array(
                    'name' => 'authorization',
                    'contents' => 'UPYUN ' . $this->config->operatorName . ':' . $signature,
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
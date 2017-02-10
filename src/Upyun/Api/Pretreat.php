<?php
namespace Upyun\Api;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Upyun\Config;
use Upyun\Signature;
use Upyun\Util;


class Pretreat {

    protected $url = 'http://p0.api.upyun.com';

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function process($source, $tasks) {
        $encodedTasks = Util::base64Json($tasks);

        $client = new Client([
            'timeout' => $this->config->timeout,
        ]);

        $params = array(
            'bucket_name' => $this->config->bucketName,
            'notify_url' => $this->config->processNotifyUrl,
            'source' => $source,
            'tasks' => $encodedTasks,
            'accept' => 'json'
        );

        $path = '/pretreatment/';
        $method = 'POST';
        $signedHeaders = Signature::getHeaderSign($this->config, $method, $path);

        $response = $client->request($method, $this->url . $path, [
            'headers' => $signedHeaders,
            'form_params' => $params
        ]);

        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }


    public function query($taskIds, $path) {
        $client = new Client([
            'timeout' => $this->config->timeout,
        ]);

        $params = array(
            'service' => $this->config->bucketName,
            'task_ids' => implode(',', $taskIds)
        );
        $path = $path . '?' . http_build_query($params);

        $method = 'GET';
        $url = $this->url . $path;
        $signedHeaders = Signature::getHeaderSign($this->config, $method, $path);
        $response = $client->request($method, $url, [
            'headers' => $signedHeaders
        ]);

        if ($response->getStatusCode() === 200) {
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);
            if (is_array($result)) {
                return $result['tasks'];
            }
        }
        return false;
    }
}

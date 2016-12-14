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
            'notify_url' => $this->config->videoNotifyUrl,
            'source' => $source,
            'tasks' => $encodedTasks,
            'accept' => 'json'
        );

        $url = $this->url . '/pretreatment';
        $signature = Signature::getSignature($this->config, $params, Signature::SIGN_VIDEO);
        $response = $client->request('POST', $url, [
            'headers' => array('Authorization' => "UPYUN {$this->config->operatorName}:$signature"),
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
            'bucket_name' => $this->config->bucketName,
            'task_ids' => implode(',', $taskIds)
        );

        $url = $this->url . $path;
        $signature = Signature::getSignature($this->config, $params, Signature::SIGN_VIDEO);
        $response = $client->request('GET', $url, [
            'headers' => array('Authorization' => "UPYUN {$this->config->operatorName}:$signature"),
            'query' => $params
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
<?php
namespace Upyun;

class Video {
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $bucketConfig) {
        $this->setConfig($bucketConfig);
    }

    public function setConfig(Config $bucketConfig) {
        $this->config = $bucketConfig;
    }

    public function pretreat($source, $notifyUrl, $tasks) {
        $postParams['tasks'] = Util::base64Json($tasks);
        $postParams['source'] = $source;
        $postParams['notify_url'] = $notifyUrl;
        $postParams['bucket_name'] = $this->config->bucketName;
        $sign = Signature::getSignature(
            $this->config,
            $postParams,
            Signature::SIGN_VIDEO
        );
        
        $response = Request::post(
            sprintf('http://%s/%s/', Config::ED_VIDEO, 'pretreatment'),
            array('Authorization' => "UpYun {$this->config->operatorName}:$sign"),
            $postParams
        );

        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }


        $taskIds = json_decode($response->body, true);
        return $taskIds;
    }


    public function status($taskIds) {
        $limit = 20;
        if(count($taskIds) <= $limit) {
            $taskIds = implode(',', $taskIds);
        } else {
            throw new \Exception('can not query more than ' . $limit . ' tasks at one time!');
        }

        $query['task_ids'] = $taskIds;
        $query['bucket_name'] = $this->config->bucketName;
        $sign = Signature::getSignature(
            $this->config,
            $query,
            Signature::SIGN_VIDEO
        );

        $response = Request::get(
            sprintf('http://%s/%s/', Config::ED_VIDEO, 'status'),
            array('Authorization' => "UpYun {$this->config->operatorName}:$sign"),
            $query
        );

        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }

        $status = json_decode($response->body, true);
        return $status;
    }

    public function callbackSignVerify() {
        $callbackKeys = array(
            'bucket_name',
            'status_code',
            'path',
            'description',
            'task_id',
            'info',
            'signature',
        );
        $callbackParams = array();
        foreach($callbackKeys as $key) {
            if(isset($_POST[$key])) {
               $callbackParams[$key] = Util::trim($_POST[$key]);
            }
        }

        if(isset($callbackParams['signature'])) {
            $sign = $callbackParams['signature'];
            unset($callbackParams['signature']);
            return $sign === Signature::getSignature(
                $this->config,
                $callbackParams,
                Signature::SIGN_VIDEO
            );
        }

        if(isset($data['non_signature'])) {
            $sign = $callbackParams['non_signature'];
            unset($callbackParams['non_signature']);
            return $sign === Signature::getSignature(
                $this->config,
                $callbackParams,
                Signature::SIGN_VIDEO_NO_OPERATOR
            );
        }
        return false;
    }
}
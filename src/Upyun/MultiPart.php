<?php
namespace Upyun;

/**
 * Class MultiPart
 * @package Upyun
 * 分块上传
 */
class MultiPart{

    /**
     * @var BucketConfig
     */
    protected $config;
    /**
     * @var int: 分块大小
     */
    protected $blockSize;

    public function __construct(BucketConfig $bucketConfig) {
        $this->setConfig($bucketConfig);
    }

    public function setConfig(BucketConfig $bucketConfig) {
        $this->config = $bucketConfig;
    }

    /**
     * 使用分块上传本地文件到 UPYUN 服务
     * @param LocalFile $file
     * @param $remotePath
     * @param array $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function upload(LocalFile $file, $remotePath, $data = array()) {
        $initResponse = $this->init($file, $remotePath, $data);
        $blockStatus = $initResponse->status;
        $newBlockStatus = $blockStatus;

        for($blockId = 0; $blockId < $initResponse->blocks; $blockId++) {
            if($blockStatus[$blockId] === 0) {
                $return = $this->blockUpload($initResponse, $blockId, $file);
                $newBlockStatus = $return->status;
            }
        }

        $file->closeHandler();
        if(array_sum($newBlockStatus) === $initResponse->blocks) {
            $endResponse = $this->end($initResponse, $data);
            return $endResponse;
        } else {
            throw new \Exception(sprintf("chunk upload failed! status is : [%s]", implode(',', $newBlockStatus)));
        }
    }

    /**
     * 初始化分块上传
     * @param LocalFile $file
     * @param $remotePath
     * @param array $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function init(LocalFile $file, $remotePath, $data = array()) {
        $this->blockSize = $this->getSuitAbleBlockSize($file->getFileSize());
        $metaData = array(
            'expiration' => time() + 60,
            'file_blocks' => ceil($file->getFileSize() / $this->blockSize),
            'file_hash' => $file->getMd5FileHash(),
            'file_size' => $file->getFileSize(),
            'path' => $remotePath
        );
        $metaData = array_merge($metaData, $data);
        $policy = $this->config->getPolicy($metaData);
        $signature = $this->config->getSignature($metaData, BucketConfig::SIGN_MULTIPART);
        $postData = compact('policy', 'signature');

        $response = Request::post(
            sprintf('http://%s/%s/', BucketConfig::ED_FORM, $this->config->bucketName),
            array(),
            $postData
        );
        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }
        $blocksInfo = json_decode($response->body);
        return $blocksInfo;
    }

    /**
     * 上传单个块
     *
     * @param $blocksInfo: init() 方法的返回值
     * @param $blockId: 当前上传的块 ID，从 0 计数
     * @param LocalFile $file
     * @param array $data
     *
     * @return mixed
     */
    public function blockUpload($blocksInfo, $blockId, LocalFile $file, $data = array()) {
        $startPosition = $blockId * $this->blockSize;
        $endPosition = $blockId >= $blocksInfo->blocks - 1 ? $file->getFileSize() : $startPosition + $this->blockSize;
        $fileBlock = $file->readBlock($startPosition, $endPosition);
        $hash = md5($fileBlock);

        $metaData = array(
            'save_token' => $blocksInfo->save_token,
            'expiration' => $blocksInfo->expired_at,
            'block_index' => $blockId,
            'block_hash' => $hash,
        );
        $metaData = array_merge($metaData, $data);
        $postData['policy'] = $this->config->getPolicy($metaData);
        $postData['signature'] = $this->config->getSignature(
            $metaData,
            BucketConfig::SIGN_MULTIPART,
            $blocksInfo->token_secret
        );
        $postData['file'] = array('data' => $fileBlock);

        $newBlocksInfo = Util::multiPartPost(
            $postData,
            sprintf('http://%s/%s/', BucketConfig::ED_FORM, $this->config->bucketName)
        );

        return json_decode($newBlocksInfo);
    }

    /**
     * 结束分块上传，所有文件块上传完毕后，再调用该方法
     * @param $initResponse
     * @param array $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function end($initResponse, $data = array()) {
        $metaData['save_token'] = $initResponse->save_token;
        $metaData['expiration'] = $initResponse->expired_at;
        $metaData = array_merge($metaData, $data);
        $policy = $this->config->getPolicy($metaData);
        $signature = $this->config->getSignature($metaData, BucketConfig::SIGN_MULTIPART, $initResponse->token_secret);
        $postData = compact('policy', 'signature');

        $response = Request::post(
            sprintf('http://%s/%s/', BucketConfig::ED_FORM, $this->config->bucketName),
            array(),
            $postData
        );
        if($response->status_code !== 200) {
            $body = json_decode($response->body, true);
            throw new \Exception(sprintf('%s, with x-request-id=%s', $body['msg'], $body['id']), $body['code']);
        }
        $responseData = json_decode($response->body);
        return $responseData;
    }

    /**
     * 根据本地文件大小，获取合适的分块大小
     * @param $fileSize
     *
     * @return int
     */
    private function getSuitAbleBlockSize($fileSize) {
        switch($fileSize) {
            case $fileSize <= 1 * 1024 * 1024;
                return 100 * 1024;
            case $fileSize <= 5 * 1024 * 1024;
                return 1 * 1024 * 1024;
            case $fileSize <= 20 * 1024 * 1024;
                return 2 * 1024 * 1024;
            default:
                return 5 * 1024 * 1024;
        }
    }
}
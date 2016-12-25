<?php
namespace Upyun\Api;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Upyun\Config;
use Upyun\Signature;
use Upyun\Util;

class Multi {
    /**
     * @var Config
     */
    protected $config;

    protected $url;

    public function __construct(Config $config) {
        $this->config = $config;
        $this->url = ($this->config->useSsl ? 'https://' : 'http://') . Config::ED_FORM . '/'.
                     $this->config->bucketName;
    }

    /**
     * @param string $path 文件存储路径
     * @param Psr7\stream $stream 通过 `Psr7\stream_for` 方法格式化的流资源
     * @param string $fileHash 文件 md5 值
     * @param array $params 其他自定义参数
     *
     * @return Psr7\Response
     * @throws \Exception
     */
    public function upload($path, $stream, $fileHash, $params = []) {
        $path = '/' . ltrim($path, '/');
        $initInfo = $this->initRequest($path, $stream, $fileHash, $params);
        $blockStatus = $initInfo->status;

        $newBlockStatus = $blockStatus;

        for($blockId = 0; $blockId < $initInfo->blocks; $blockId++) {
            if($blockStatus[$blockId] === 0) {
                $return = $this->blockUpload($initInfo, $blockId, $stream);
                $newBlockStatus = $return->status;
            }
        }

        if(array_sum($newBlockStatus) === $initInfo->blocks) {
            return $this->endRequest($initInfo, $params);
        } else {
            throw new \Exception(sprintf("chunk upload failed! current every block status is : [%s]", implode(',', $newBlockStatus)));
        }
    }

    private function initRequest($path, Psr7\Stream $stream, $fileHash, $params) {
        $metaData = array(
            'expiration' => time() + $this->config->blockExpiration,
            'file_blocks' => ceil($stream->getSize() / $this->config->maxBlockSize),
            'file_hash' => $fileHash,
            'file_size' => $stream->getSize(),
            'path' => $path
        );

        $metaData = array_merge($metaData, $params);
        $policy = Util::base64Json($metaData);
        $signature = Signature::getSignature(
            $this->config,
            $metaData,
            Signature::SIGN_MULTIPART
        );
        $postData = compact('policy', 'signature');

        $client = new Client();
        $response = $client->request('POST', $this->url, [
            'form_params' => $postData,
        ]);

        $initInfo = json_decode($response->getBody()->getContents());
        return $initInfo;
    }

    private function blockUpload($blocksInfo, $blockId, Psr7\Stream $stream, $params = []) {
        $startPosition = $blockId * $this->config->maxBlockSize;
        $endPosition   = $blockId >= $blocksInfo->blocks - 1 ? $stream->getSize() : $startPosition + $this->config->maxBlockSize;

        $stream->seek($startPosition);

        $fileBlock = $stream->read($endPosition - $startPosition);

        $metaData = array(
            'save_token' => $blocksInfo->save_token,
            'expiration' => $blocksInfo->expired_at,
            'block_index' => $blockId,
            'block_hash' => md5($fileBlock),
        );
        $metaData = array_merge($metaData, $params);
        $postData['policy'] = Util::base64Json($metaData);
        $postData['signature'] = Signature::getSignature(
            $this->config,
            $metaData,
            Signature::SIGN_MULTIPART,
            $blocksInfo->token_secret
        );

        $multipart = [];
        foreach($postData as $key => $value) {
           $multipart[] = ['name' => $key, 'contents' => $value];
        }
        $multipart[] = [
            'name' => 'file',
            'contents' => $fileBlock,
            'filename' => 'file',  //this value must be file
            'headers' => ['Content-Type' => 'application/octet-stream']
        ];
        $postData['file'] = $fileBlock;

        $client = new Client();
        $response = $client->request('POST', $this->url, [
            'multipart' => $multipart,
        ]);

        return json_decode($response->getBody()->getContents());
    }

    private function endRequest($initInfo, $data = array()) {
        $metaData['save_token'] = $initInfo->save_token;
        $metaData['expiration'] = $initInfo->expired_at;

        $metaData = array_merge($metaData, $data);
        $policy = Util::base64Json($metaData);
        $signature = Signature::getSignature(
            $this->config,
            $metaData,
            Signature::SIGN_MULTIPART,
            $initInfo->token_secret
        );
        $postData = compact('policy', 'signature');

        $client = new Client();
        $response = $client->request('POST', $this->url, [
            'form_params' => $postData
        ]);
        return $response;
    }
}

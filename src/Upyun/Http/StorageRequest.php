<?php

namespace Upyun\Http;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7;
use Upyun\Config;

class StorageRequest {
    /**
     * @var Config
     */
    public $config;

    public $uri;
    public $method;
    public $requestTarget;
    public $headers = [];

    protected $useBlock = false;
    protected $files = [];


    public function __construct(Config $config) {
        $this->config = $config;
        $this->uri = Config::$restApiEndPoint . '/' . $config->bucketName;
    }

    public function withRequestTarget($storagePath) {
        if(preg_match('#\s#', $storagePath)) {
            throw new \Exception(
                'Invalid storage path; cannot contain whitespace'
            );
        }

        $this->requestTarget = $storagePath;
        return $this;
    }

    /**
     * @param string|resource $file
     */
    public function addFile($file) {
        $stream = Psr7\stream_for($file);
        $this->files[] = $stream;

        $size = $stream->getSize();
        $this->chooseBlockOrRestApi($size);

        return $this;
    }

    public function withMethod($method) {
        $this->method = strtoupper($method);
        return $this;
    }
    public function getUri() {
        //TODO 根据配置文件生成 uri
        if($this->useBlock) {
            return $this->uri;
        } else {
            return $this->uri . $this->requestTarget;
        }
    }

    public function send() {
        $client = new Curl();
        return $client->exec($this);
    }

    public function withHeader($header, $value) {
        $header = strtolower(trim($header));

        $this->headers[$header] = $value;
        return $this;
    }

    public function withHeaders($headers) {
        if(is_array($headers)) {
            foreach ($headers as $header => $value) {
                $this->withHeader($header, $value);
            }
        }
        return $this;
    }

    private function chooseBlockOrRestApi($fileSize) {
        if($this->useBlock) {
            return $this;
        }

        if($this->config->uploadType === 'BLOCK') {
            $this->useBlock = true;
        } else if($this->config->uploadType === 'AUTO' &&
                  $fileSize >= $this->config->sizeBoundary ) {
            $this->useBlock = true;
        }

        $this->uri = $this->useBlock ? Config::ED_FORM : Config::$restApiEndPoint;
    }
}
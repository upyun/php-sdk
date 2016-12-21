<?php

namespace Upyun\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Upyun\Config;
use Upyun\Signature;

class Rest {
    /**
     * @var Config
     */
    protected $config;

    protected $endpoint;
    protected $method;
    protected $storagePath;
    public $headers = [];

    /**
     * @var Psr7\Stream
     */
    protected $file;


    public function __construct(Config $config) {
        $this->config   = $config;
        $this->endpoint = Config::$restApiEndPoint . '/' . $config->bucketName;
    }
    
    public function request($method, $storagePath) {
        $this->method = strtoupper($method);
        $this->storagePath = '/' . ltrim($storagePath, '/');
        return $this;
    }


    /**
     * @param string|resource $file
     *
     * @return $this
     */
    public function withFile($file) {
        $stream = Psr7\stream_for($file);
        $this->file = $stream;

        return $this;
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function send() {
        $client = new Client([
            'timeout' => $this->config->timeout,
        ]);

        $url = ($this->config->useSsl ? 'https://' : 'http://') . $this->endpoint . $this->storagePath;
        $bodySize = 0;
        $body = null;
        if($this->file && $this->method === 'PUT') {
            $bodySize = $this->file->getSize();
            $body = $this->file;
        }
        
        $authHeader = Signature::getRestApiSignHeader($this->config, $this->method, $this->storagePath, $bodySize);
        $response = $client->request($this->method, $url, [
            'headers' => array_merge($authHeader, $this->headers),
            'body' => $body
        ]);

        return $response;
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
}

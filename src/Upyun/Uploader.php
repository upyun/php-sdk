<?php
namespace Upyun;

use Upyun\Api\Rest;
use Upyun\Api\Multi;
use GuzzleHttp\Psr7;

class Uploader {
    /**
     * @var Config
     */
    protected $config;

    protected $useBlock = false;


    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function upload($path, $file, $params) {
        $stream = Psr7\stream_for($file);
        $size = $stream->getSize();
        $useBlock = $this->needUseBlock($size);
        if(! $useBlock) {
            $req = new Rest($this->config);
            return $req->request('PUT', $path)
                       ->withHeaders($params)
                       ->withFile($stream)
                       ->send();
        } else {
            $req = new Multi($this->config);
            return $req->upload($path, $stream, Util::md5Hash($file), $params);
        }
    }

    private function needUseBlock($fileSize) {
        if($this->config->uploadType === 'BLOCK') {
            return true;
        } else if($this->config->uploadType === 'AUTO' &&
                  $fileSize >= $this->config->sizeBoundary ) {
            return true;
        } else {
            return false;
        }
    }
}

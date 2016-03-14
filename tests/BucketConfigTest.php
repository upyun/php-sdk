<?php

use Upyun\BucketConfig;

class BucketConfigTest extends PHPUnit_Framework_TestCase{

    /**
     * @var BucketConfig;
     */
    public $config;
    public function setUp() {
        $this->config = new BucketConfig('bucket', 'operator', 'password');
    }

    public function testGetRestApiSign() {
        $sign = $this->config->getRestApiSign('GET', '/sub', 'Wed, 29 Oct 2014 02:26:58 GMT', 0);
        $this->assertEquals('03db45e2904663c5c9305a9c6ed62af3', $sign);
    }

    public function testGetRequestUrl() {
        $url = $this->config->getRestApiUrl('/sub');
        $this->assertEquals('http://v0.api.upyun.com/bucket/sub', $url);
    }
}
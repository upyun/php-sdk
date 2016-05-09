<?php
namespace Upyun\Tests;
use Upyun\BucketConfig;

class BucketConfigTest  extends \PHPUnit_Framework_TestCase{
    /**
     * @var BucketConfig;
     */
    public $config;

    public function setUp() {
        $this->config = new BucketConfig('bucket', 'operator', 'password');
    }
    
    public function testGetRequestUrl() {
        $url = $this->config->getRestApiUrl('/sub');
        $this->assertEquals('http://v0.api.upyun.com/bucket/sub', $url);
    }
}
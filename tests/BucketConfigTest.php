<?php
namespace Upyun\Tests;
use Upyun\Config;

class BucketConfigTest  extends \PHPUnit_Framework_TestCase{
    /**
     * @var Config;
     */
    public $config;

    public function setUp() {
        $this->config = new Config('bucket', 'operator', 'password');
    }
    
    public function testGetRequestUrl() {
        $url = $this->config->getRestApiUrl('/sub');
        $this->assertEquals('http://v0.api.upyun.com/bucket/sub', $url);
    }
}
<?php
namespace Upyun\Tests;
use Upyun\Signature;
use Upyun\Config;

class SignatureTest extends \PHPUnit_Framework_TestCase{

    /**
     * @var Config;
     */
    public $config;

    public function setUp() {
        $this->config = new Config('bucket', 'operator', 'password');
    }

    public function testGetSignature() {
        $sign = Signature::getSignature($this->config, array('a' => 'a', 'b' => 'b'), Signature::SIGN_MULTIPART, '123');
        $this->assertEquals($sign , '2aa0afd612df8fab4b3fded36c396234');
    }

    public function testGetFormSignature () {
        $config = new Config('upyun-temp', 'upyun', 'upyun520');
        $sign = Signature::getFormSignature($config, array(
            'save-key' => '/demo.jpg',
            'expiration' => '1478674618',
            'date' => 'Wed, 9 Nov 2016 14:26:58 GMT',
            'content-md5' => '7ac66c0f148de9519b8bd264312c4d64'
        ));
        $this->assertEquals($sign['policy'], 'eyJzYXZlLWtleSI6Ii9kZW1vLmpwZyIsImV4cGlyYXRpb24iOiIxNDc4Njc0NjE4IiwiZGF0ZSI6IldlZCwgOSBOb3YgMjAxNiAxNDoyNjo1OCBHTVQiLCJjb250ZW50LW1kNSI6IjdhYzY2YzBmMTQ4ZGU5NTE5YjhiZDI2NDMxMmM0ZDY0IiwiYnVja2V0IjoidXB5dW4tdGVtcCJ9');
        $this->assertEquals($sign['signature'], '5o1GFkCid+adWo7zf1HPc0b80QM=');
    }
}
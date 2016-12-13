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
}
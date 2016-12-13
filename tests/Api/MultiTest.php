<?php
namespace Upyun\Tests\Api;

use Upyun\Config;
use Upyun\Api\Multi;
use GuzzleHttp\Psr7;
use Upyun\Upyun;

class MultiTest extends \PHPUnit_Framework_TestCase{

    /**
     * @var Multi;
     */
    public $multiPart;

    public function setUp() {
        $config = new Config(BUCKET, USER_NAME, PWD);
        $config->setFormApiKey('Mv83tlocuzkmfKKUFbz2s04FzTw=');
        $this->multiPart = new Multi($config);
    }

    public function testUpload() {
        $filePath = __DIR__ . '/../assets/sample.jpeg';
        $stream = Psr7\stream_for(fopen($filePath, 'rb'));
        $r = $this->multiPart->upload('test-sample.jpeg', $stream, md5_file($filePath));
        $this->assertEquals($r->getStatusCode(), 200);
    }
}

<?php
namespace Upyun\Tests;

use Upyun\BucketConfig;
use Upyun\MultiPart;
use Upyun\LocalFile;
use Upyun\Filesystem;

class MultiPartTest extends \PHPUnit_Framework_TestCase{

    /**
     * @var MultiPart;
     */
    public $multiPart;

    public function setUp() {
        $config = new BucketConfig(BUCKET, USER_NAME, PWD);
        $config->setFormApiKey('Mv83tlocuzkmfKKUFbz2s04FzTw=');
        $this->multiPart = new MultiPart($config);
    }

    public function testInit() {
        $file = new LocalFile(__DIR__ . '/assets/sample.jpeg');
        $blockInfo = $this->multiPart->init($file, 'test-sample.jpeg');
        $this->assertEquals($blockInfo->blocks, 1);
    }

    /**
     * @depends testInit
     */
    public function testBlockUpload() {
        $file = new LocalFile(__DIR__ . '/assets/sample.jpeg');
        $blockInfo = $this->multiPart->init($file, 'test-sample.jpeg');
        $newBlockInfo = $this->multiPart->blockUpload($blockInfo, 0, $file);
        $this->assertEquals($newBlockInfo->blocks, 1);
        $this->assertEquals($newBlockInfo->status[0], 1);
    }

    public function testUpload() {
        $file = new LocalFile(__DIR__ . '/assets/sample.jpeg');
        $r = $this->multiPart->upload($file, 'test-sample.jpeg');
        $this->assertEquals($r->path, '/test-sample.jpeg');
        $this->assertEquals($r->file_size, PIC_SIZE);
    }
}
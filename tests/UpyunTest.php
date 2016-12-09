<?php
namespace Upyun\Tests;
use Upyun\Config;
use Upyun\Upyun;

class UpyunTest extends \PHPUnit_Framework_TestCase{

    /**
     * @var Upyun
     */
    public static $upyun;

    protected static $tempFilePath;

    public static function setUpBeforeClass() {
        self::$upyun        = new Upyun(new Config(BUCKET, USER_NAME, PWD));
        self::$tempFilePath = __DIR__ . '/assets/test.txt';
        touch(self::$tempFilePath);
    }

    public static function tearDownAfterClass() {
        unlink(self::$tempFilePath);
    }

    public function testWriteString() {
        $filename = 'test.txt';
        $content = 'test file content';
        self::$upyun->write($filename, $content);
        $size = getUpyunFileSize($filename);
        $this->assertEquals($size, strlen($content));
    }

    public function testWriteStream() {
        $filename = 'test.jpeg';
        $f = fopen(__DIR__ . '/assets/sample.jpeg', 'rb');
        if(!$f) {
            throw new \Exception('open test file failed!');
        }
        self::$upyun->write($filename, $f);
        $size = getUpyunFileSize($filename);
        $this->assertEquals($size, PIC_SIZE);
    }

    public function testWriteWithException() {
        $fs = new Upyun(new Config(BUCKET, USER_NAME, 'error-password'));
        try {
            $fs->write('test.txt', 'test file content');
        } catch(\Exception $e) {
            return ;
        }
        throw new \Exception('should get sign error.');
    }

    /**
     * @depends testWriteString
     */
    public function testReadFile() {
        $name = 'test.txt';
        $str = 'test file content 2';
        self::$upyun->write($name, $str);

        //读取内容写入字符串
        $content = self::$upyun->read($name);
        $this->assertEquals($content, $str);

        //读取内容写入文件流
        $this->assertTrue(self::$upyun->read($name, fopen(self::$tempFilePath, 'wb')));
        $this->assertEquals($str, file_get_contents(self::$tempFilePath));
    }

    /**
     * @depends testWriteString
     * @depends testReadFile
     */
    public function testDeleteFile() {
        self::$upyun->write('test.txt', 'test file content 3');
        $r = self::$upyun->delete('test.txt');
        try {
            self::$upyun->read('test.txt');
        } catch(\Exception $e) {
            return ;
        }
        throw new \Exception('delete file failed');
    }

    /**
     * @expectedException \Exception
     */
    public function testDeleteNotExistsFile() {
        self::$upyun->delete('not-exists-test.txt');
    }

    /**
     */
    public function testHas() {
        sleep(1); // upyun server limit delete rate, so..
        self::$upyun->write('test.txt', 'test file content 4');
        $this->assertEquals(self::$upyun->has('test.txt'), true);
        self::$upyun->delete('test.txt');
        $this->assertEquals(self::$upyun->has('test.txt'), false);
    }

    /**
     * @depends testWriteString
     * @depends testDeleteFile
     */
    public function testInfo() {
        self::$upyun->write('test.txt', 'test file content 4');
        $info = self::$upyun->info('test.txt');
        $this->assertEquals($info['x-upyun-file-type'], 'file');
        $this->assertEquals($info['x-upyun-file-size'], 19);
    }

    /**
     */
    public function testCreateDir() {
        self::$upyun->createDir('/test-dir');
        $this->assertEquals(self::$upyun->has('/test-dir'), true);
        self::$upyun->createDir('/test-dir2/');
        $this->assertEquals(self::$upyun->has('/test-dir2'), true);
    }

    public function testReadDir() {
        $list = self::$upyun->read('/test-dir2/');
        $this->assertEquals($list['is_end'], true);
        self::$upyun->write('/test-dir2/test.txt', 'test file content 5');
        $list = self::$upyun->read('/test-dir2/');
        $this->assertEquals($list['is_end'], true);
        $this->assertEquals(count($list['files']), 1);
        $file = $list['files'][0];
        $this->assertEquals($file['name'], 'test.txt');
        $this->assertEquals($file['type'], 'N');
        $this->assertEquals($file['size'], 19);
    }

    /**
     * @depends testCreateDir
     * @depends testHas
     */
    public function testDeleteDir() {
        self::$upyun->createDir('/test-dir');
        $this->assertEquals(self::$upyun->has('/test-dir'), true);
        self::$upyun->deleteDir('/test-dir');
        $this->assertEquals(self::$upyun->has('/test-dir'), false);
    }

    public function testUsage() {
        $size = self::$upyun->usage();
        $this->assertTrue($size > 0);
    }

    public function testPurge() {
        $urls = self::$upyun->purge(getFileUrl('test.txt'));
        $this->assertTrue(empty($urls));

        $invalidUrl = 'http://xxxx.b0.xxxxxxxx-upyun.com/test.txt';
        $urls = self::$upyun->purge($invalidUrl);
        $this->assertTrue(count($urls) === 1);
        $this->assertTrue($urls[0] === $invalidUrl);
    }
}
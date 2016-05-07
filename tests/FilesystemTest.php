<?php
namespace Upyun\Tests;
use Upyun\BucketConfig;
use Upyun\Filesystem;

class FilesystemTest extends \PHPUnit_Framework_TestCase{

    /**
     * @var Filesystem
     */
    public static $filesystem;

    protected static $tempFilePath;

    public static function setUpBeforeClass() {
        self::$filesystem = new Filesystem(new BucketConfig(BUCKET, USER_NAME, PWD));
        self::$tempFilePath = __DIR__ . '/assets/test.txt';
        touch(self::$tempFilePath);
    }

    public static function tearDownAfterClass() {
        unlink(self::$tempFilePath);
    }

    public function testWriteString() {
        $filename = 'test.txt';
        $content = 'test file content';
        self::$filesystem->write($filename, $content);
        $size = getUpyunFileSize($filename);
        $this->assertEquals($size, strlen($content));
    }

    public function testWriteStream() {
        $filename = 'test.jpeg';
        $f = fopen(__DIR__ . '/assets/sample.jpeg', 'rb');
        if(!$f) {
            throw new \Exception('open test file failed!');
        }
        self::$filesystem->write($filename, $f);
        $size = getUpyunFileSize($filename);
        $this->assertEquals($size, PIC_SIZE);
    }

    /**
     * @expectedException \Exception
     */
    public function testWirteWithException() {
        $fs = new Filesystem(new BucketConfig(BUCKET, USER_NAME, 'error-password'));
        $fs->write('test.txt', 'test file content');
    }

    /**
     * @depends testWriteString
     */
    public function testReadFile() {
        $name = 'test.txt';
        $str = 'test file content 2';
        self::$filesystem->write($name, $str);

        //读取内容写入字符串
        $content = self::$filesystem->read($name);
        $this->assertEquals($content, $str);

        //读取内容写入文件流
        $this->assertTrue(self::$filesystem->read($name, fopen(self::$tempFilePath, 'wb')));
        $this->assertEquals($str, file_get_contents(self::$tempFilePath));
    }

    /**
     * @depends testWriteString
     * @depends testReadFile
     */
    public function testDeleteFile() {
        self::$filesystem->write('test.txt', 'test file content 3');
        $r = self::$filesystem->delete('test.txt');
        try {
            self::$filesystem->read('test.txt');
        } catch(\Exception $e) {
            return $this->assertEquals($e->getCode(), 40400001);
        }
        throw new \Exception('delete file failed');
    }

    /**
     * @expectedException \Exception
     */
    public function testDeleteNotExistsFile() {
        self::$filesystem->delete('not-exists-test.txt');
    }

    /**
     * @depends testWriteString
     * @depends testDeleteFile
     */
    public function testHas() {
        self::$filesystem->write('test.txt', 'test file content 4');
        $this->assertEquals(self::$filesystem->has('test.txt'), true);
        self::$filesystem->delete('test.txt');
        $this->assertEquals(self::$filesystem->has('test.txt'), false);
    }

    /**
     * @depends testWriteString
     * @depends testDeleteFile
     */
    public function testInfo() {
        $t = time();
        self::$filesystem->write('test.txt', 'test file content 4');
        $info = self::$filesystem->info('test.txt');
        $this->assertEquals($info['x-upyun-file-type'], 'file');
        $this->assertEquals($info['x-upyun-file-size'], 19);
        $this->assertTrue($info['x-upyun-file-date'] >= $t);

        self::$filesystem->delete('test.txt');
        $this->assertTrue(count(self::$filesystem->info('test.txt')) === 0);
    }

    /**
     * @depends testHas
     */
    public function testCreateDir() {
        self::$filesystem->createDir('/test-dir');
        $this->assertEquals(self::$filesystem->has('/test-dir'), true);
        self::$filesystem->createDir('/test-dir2/');
        $this->assertEquals(self::$filesystem->has('/test-dir2'), true);
    }

    public function testReadDir() {
        $t = time();
        $list = self::$filesystem->read('/test-dir2/');
        $this->assertEquals($list['is_end'], true);
        self::$filesystem->write('/test-dir2/test.txt', 'test file content 5');
        $list = self::$filesystem->read('/test-dir2/');
        $this->assertEquals($list['is_end'], true);
        $this->assertEquals(count($list['files']), 1);
        $file = $list['files'][0];
        $this->assertEquals($file['name'], 'test.txt');
        $this->assertEquals($file['type'], 'N');
        $this->assertEquals($file['size'], 19);
        $this->assertTrue($file['time'] >= $t);
    }

    /**
     * @depends testCreateDir
     * @depends testHas
     */
    public function testDeleteDir() {
        self::$filesystem->createDir('/test-dir');
        $this->assertEquals(self::$filesystem->has('/test-dir'), true);
        self::$filesystem->deleteDir('/test-dir');
        $this->assertEquals(self::$filesystem->has('/test-dir'), false);
    }

    public function testUsage() {
        $size = self::$filesystem->usage();
        $this->assertTrue($size > 0);
    }

    public function testPurge() {
        $urls = self::$filesystem->purge(getFileUrl('test.txt'));
        $this->assertTrue(empty($urls));

        $invalidUrl = 'http://xxxx.b0.xxxxxxxx-upyun.com/test.txt';
        $urls = self::$filesystem->purge($invalidUrl);
        $this->assertTrue(count($urls) === 1);
        $this->assertTrue($urls[0] === $invalidUrl);
    }
}
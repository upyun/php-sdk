<?php

class unyunTest extends PHPUnit_Framework_TestCase
{

    public function setUp(){
        $this->upyun = new UpYun(BUCKET, USER_NAME, PWD, UpYun::ED_TELECOM, 600);
    }

    public function testMakeDir(){
        $rsp = $this->upyun->makeDir('/demo/');
        $this->assertTrue(true);
    }
    /**
    * 直接上传文件
    */
    public function testDirectUpload()
    {
        $fh = fopen(PIC_PATH, 'rb');
        $rsp = $this->upyun->writeFile('/demo/sample_normal.jpeg', $fh, True);   // 上传图片，自动创建目录
        fclose($fh);
        $this->assertTrue(true , is_array($rsp));
    }
    /**
    * 直接生成缩略图，不保存原图片，仅对图片文件有效
    */
    public function testWriteFile1(){
        $opts = array(
            UpYun::X_GMKERL_TYPE    => 'square', // 缩略图类型
            UpYun::X_GMKERL_VALUE   => 150, // 缩略图大小
            UpYun::X_GMKERL_QUALITY => 95, // 缩略图压缩质量
            UpYun::X_GMKERL_UNSHARP => True // 是否进行锐化处理
        );
        $fh = fopen(PIC_PATH, 'rb');
        $rsp = $this->upyun->writeFile('/demo/sample_thumb_1.jpeg', $fh, True, $opts);   // 上传图片，自动创建目录
        fclose($fh);
        $this->assertTrue(is_array($rsp));
    }
    /**
    * 按照预先设置的缩略图类型生成缩略图类型生成缩略图，不保存原图，仅对图片空间有效
    */
    public function testWriteFile2(){
        $opts = array(
            UpYun::X_GMKERL_THUMBNAIL => 'thumbtype'
        );
        $fh = fopen(PIC_PATH, 'rb');
        $rsp = $this->upyun->writeFile('/demo/sample_thumb_2.jpeg', $fh, True, $opts);   // 上传图片，自动创建目录
        fclose($fh);
        $this->assertTrue(is_array($rsp));
    }
    /**
    * 获取空间的使用情况
    */
    public function testUsage(){
        $rsp = $this->upyun->getFolderUsage('/demo/');
        $this->assertTrue(is_float($rsp));
    }
    /**
    * 获取指定文件的目录信息
    */
    public function testFileInfo(){
        $rsp = $this->upyun->getFolderUsage('/demo/sample_normal.jpeg');
        $this->assertTrue(is_float($rsp));
    }
    /**
    * 获取目录文件列表
    */
    public function testList(){
        $rsp = $this->upyun->getList('/demo/');
        $this->assertTrue(is_array($rsp));
    }
    /**
     * 删除空间目录
     * @expectedException \Exception
     * @depends testMakeDir
     */
    public function testDelete()
    {
        $rsp = $this->upyun->delete('/demo/');
        $this->assertTrue($rsp);
    }

    /**
     * 获取错误请求的 X-Request-Id
     */
    public function testXRequestId(){
        $rsp = $this->upyun->getList('/demo/');
        $x_id = $this->upyun->getXRequestId();
        $this->assertEquals(strlen($x_id), 32);
    }
}

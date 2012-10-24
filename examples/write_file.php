<?php
require_once('../upyun.class.php');

$upyun = new UpYun('bucket', 'user', 'pwd');

try {
    echo "=========直接上传文件\r\n";
    $fh = fopen('sample.jpeg', 'rb');
    $rsp = $upyun->writeFile('/demo/sample_normal.jpeg', $fh, True);   // 上传图片，自动创建目录
    fclose($fh);
    var_dump($rsp);
    echo "=========DONE\n\r\n";

    echo "=========设置MD5校验文件完整性\r\n";
    $opts = array(
        UpYun::CONTENT_MD5 => md5(file_get_contents("sample.jpeg"))
    );
    $fh = fopen('sample.jpeg', 'rb');
    $rsp = $upyun->writeFile('/demo/sample_md5.jpeg', $fh, True, $opts);   // 上传图片，自动创建目录
    fclose($fh);
    var_dump($rsp);
    echo "=========DONE\r\n\r\n";

    echo "=========直接生成缩略图，不保存原图片，仅对图片文件有效\r\n";
    $opts = array(
        UpYun::X_GMKERL_TYPE    => 'square', // 缩略图类型
        UpYun::X_GMKERL_VALUE   => 150, // 缩略图大小
        UpYun::X_GMKERL_QUALITY => 95, // 缩略图压缩质量
        UpYun::X_GMKERL_UNSHARP => True // 是否进行锐化处理
    );
    $fh = fopen('sample.jpeg', 'rb');
    $rsp = $upyun->writeFile('/demo/sample_thumb_1.jpeg', $fh, True, $opts);   // 上传图片，自动创建目录
    fclose($fh);
    var_dump($rsp);
    echo "=========DONE\r\n\r\n";

    echo "=========按照预先设置的缩略图类型生成缩略图类型生成缩略图，不保存原图，仅对图片空间有效\r\n";
    $opts = array(
        UpYun::X_GMKERL_THUMBNAIL => 'thumbtype'
    );
    $fh = fopen('sample.jpeg', 'rb');
    $rsp = $upyun->writeFile('/demo/sample_thumb_2.jpeg', $fh, True, $opts);   // 上传图片，自动创建目录
    fclose($fh);
    var_dump($rsp);
    echo "=========DONE\r\n\r\n";
}
catch(Exception $e) {
    echo $e->getCode();
    echo $e->getMessage();
}

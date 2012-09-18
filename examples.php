<?php
require_once('upyun.class.php');

try{
    $upyun = new UpYun('bucketname', 'user', 'password', UpYun::ED_TELECOM);
    // 上传图片
    $fh = fopen('/path/to/file/image.png', 'r');
    $opts = array(
        UpYun::X_GMKERL_THUMBNAIL => 'square' // 缩略图版本，仅适用于图片空间
    );

    $upyun->writeFile('/temp/upload_demo.png', $fh, True, $opts);
    fclose($fh);
}
catch(Exception $e) {
    echo $e->getCode(); // 获取错误代码
    echo $e->getMessage();  // 获取具体错误信息
}

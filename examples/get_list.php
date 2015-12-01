<?php
require_once(dirname(__DIR__).'/vendor/autoload.php');
$config =  array(
    'user_name' => 'tester',
    'pwd' => 'grjxv2mxELR3',
    'bucket' => 'sdkimg',
    'picture_path' => dirname(__FILE__) . '/assets/sample.jpeg'
);
$upyun = new UpYun($config['bucket'], $config['user_name'], $config['pwd']);

try {
    echo "=========获取目录文件列表\r\n";
    $list = $upyun->getList('/demo/');
    var_dump($list);
    echo "=========DONE\r\n\r\n";
}
catch(Exception $e) {
    echo $e->getCode();
    echo $e->getMessage();
}

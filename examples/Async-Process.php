<?php
/**
 * 又拍云 PHP-SDK 异步云处理使用实例
 * 测试与运行例子的时，用户需要根据自己的需求填写对应的配置(User-Profle.php)，参数
 */

require  __DIR__ . '/User-Profile.php';

use Upyun\Config;
use Upyun\Upyun;

$config = new Config(SERVICE, USER_NAME, PWD);
$config->processNotifyUrl = NOTIFY_URL;
$client = new Upyun($config);

/**
 * 异步音视频处理
 * tasks参数与说明见:http://docs.upyun.com/cloud/av/
 */
function videoAsyncProcess()
{
    global $client;
    // 使用时，按文档和个人需求填写tasks
    $tasks = array(
        array(
            'type' => 'video',
            'avopts' => '/s/128x96',
            'save_as' => VIDEO_SAVE_AS,
        ));
    $resp = $client->process($tasks, Upyun::$PROCESS_TYPE_MEDIA, VIDEO_SAVE_KEY);
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 压缩
 * tasks参数与说明见:http://docs.upyun.com/cloud/unzip/
 */
function compress()
{
    global $client;
    // 使用时，按文档和个人需求填写tasks
    $tasks = array(
        array(
            'sources' => array(IMAGE_SAVE_KEY, VIDEO_SAVE_KEY),
            'save_as' => COMPRESS_SAVE,
        ));
    $resp = $client->process($tasks, Upyun::$PROCESS_TYPE_ZIP);
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 解压缩
 * tasks参数与说明见:http://docs.upyun.com/cloud/unzip/
 */
function depress()
{
    global $client;
    // 使用时，按文档和个人需求填写tasks
    $tasks = array(
        array(
            'sources' => COMPRESS_SAVE,
            'save_as' => REMOTE_DIR,
        ));
    $resp = $client->process($tasks, Upyun::$PROCESS_TYPE_UNZIP);
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 文件拉取
 * tasks参数与说明见:http://docs.upyun.com/cloud/spider/
 */
function spiderman()
{
    global $client;
    // 使用时，按文档和个人需求填写tasks
    $tasks = array(
        array(
            'url' => URL,
            'save_as' => SAVE_AS,
        ));
    $resp = $client->process($tasks, Upyun::$PROCESS_TYPE_SYNC_FILE);
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 文档转换
 * tasks参数与说明见:http://docs.upyun.com/cloud/uconvert/
 */
function fileAsyncConvert()
{
    global $client;
    // 使用时，按文档和个人需求填写tasks
    $tasks = array(
        array(
            'source' => DOC_SAVE_KEY,
            'save_as' => DOC_SAVE_AS,
        ));
    $resp = $client->process($tasks, Upyun::$PROCESS_TYPE_CONVERT);
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 异步图片拼接
 * tasks参数与说明见:http://docs.upyun.com/cloud/async_image/
 */
function imageAsyncJoint()
{
    global $client;
    // 使用时，按文档和个人需求填写tasks
    $imageMatrix = array(
        array(
            '/12/6.jpg',
            '/12/6.jpg'
        ));
    $tasks = array(
        array(
            'image_matrix' => $imageMatrix,
            'save_as' => IMAGE_SAVE_AS,
        ));
    $resp = $client->process($tasks, Upyun::$PROCESS_TYPE_STITCH);
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}


/**
 * 接口调用
 */
videoAsyncProcess();
compress();
depress();
spiderman();
fileAsyncConvert();
imageAsyncJoint();
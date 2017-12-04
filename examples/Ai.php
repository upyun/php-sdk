<?php
/**
 * 又拍云 PHP-SDK 人工智能使用实例
 * 测试与运行例子的时，用户需要根据自己的需求填写对应的配置(User-Profle.php)，参数
 */

require  __DIR__ . '/User-Profile.php';

use Upyun\Config;
use Upyun\Api\Pretreat;
use Upyun\Api\SyncVideo;

$config = new Config(SERVICE, USER_NAME, PWD);
$config->processNotifyUrl = NOTIFY_URL;

$client = new Pretreat($config);
$liveClient = new SyncVideo($config);

/**
 * 异步内容识别通用接口
 */
function asyncAudit($tasks, $appName)
{
    global $client;
    $options = array('app_name' => $appName);
    $resp = $client->process($tasks, $options);
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 内容识别(有存储)-图片
 * tasks参数与说明见:http://docs.upyun.com/ai/audit/
 */
function imageAsyncAudit()
{
    // 使用时，按文档和个人需求填写tasks
    $tasks = array(
        array(
            'source' => IMAGE_SAVE_KEY
        ));
    asyncAudit($tasks, 'imgaudit');
}

/**
 * 内容识别(有存储)-视频点播
 * tasks参数与说明见:http://docs.upyun.com/ai/audit/
 */
function videoAsyncAudit()
{
    // 使用时，按文档和个人需求填写tasks
    $tasks = array(
        array(
            'source' => VIDEO_SAVE_KEY
        ));
    asyncAudit($tasks, 'videoaudit');
}

/**
 * 内容识别(有存储)-视频直播
 * params参数与说明见:http://docs.upyun.com/ai/audit/
 */
function liveAudit()
{
    global $liveClient;
    // 使用时，按文档和个人需求填写params
    $params = array(
        'service' => SERVICE,
        'source' => RTMP_SOURCE,
        'save_as' => '/{year}/{mon}/{day}/{hour}_{min}_{sec}.jpg',
        'notify_url' => NOTIFY_URL,
    );
    $resp = $liveClient->process($params, '/liveaudit/create');
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 内容识别(有存储)-视频直播取消
 * params参数与说明见:http://docs.upyun.com/ai/audit/
 */
function liveAuditCancel($taskID)
{
    global $liveClient;
    // 使用时，按文档和个人需求填写params
    $params = array(
        'service' => SERVICE,
        'task_id' => $taskID,
    );
    $resp = $liveClient->process($params, '/liveaudit/cancel');
    echo json_encode($resp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}


/**
 * 接口调用
 */
imageAsyncAudit();
videoAsyncAudit();
liveAudit();
liveAuditCancel('064ca517cb85e708796f33e378b9b4cd');
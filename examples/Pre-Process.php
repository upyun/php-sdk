<?php
/**
 * 又拍云 PHP-SDK 上传预处理使用实例
 * 测试与运行例子的时，用户需要根据自己的需求填写对应的配置(User-Profle.php)，参数
 */

require  __DIR__ . '/User-Profile.php';

use Upyun\Config;
use Upyun\Upyun;

$config = new Config(SERVICE, USER_NAME, PWD);
$client = new Upyun($config);


/**
 * 通用form上传预处理
 */
function formAsyncPreProcess($file, $key, $apps=array())
{
    global $client;
    $fd = fopen($file, 'r');
    if ($fd != NULL)
    {
        // 使用时，按文档和个人需求填写params
        $params = array(
            'notify-url' => NOTIFY_URL,
            'apps' => $apps
        );
        echo $client->write($key, $fd, $params, true);
    }
    else
    {
        echo 'cannt open file:' . $file;
    }
}

/**
 * 图片异步上传预处理
 * http://docs.upyun.com/cloud/image/
 */
function formImageAsyncProcess()
{
    // 使用时，按文档和个人需求填写apps
    $apps = array(array(
        'name' => 'thumb',
        'x-gmkerl-thumb' => '/format/png',
        'save_as' => IMAGE_SAVE_AS,
    ));
    formAsyncPreProcess(IMAGE_FILE, IMAGE_SAVE_KEY, $apps);
}

/**
 * 图片同步上传预处理
 * http://docs.upyun.com/cloud/image/
 */
function formImageSyncProcess()
{
    global $client;
    $fd = fopen(IMAGE_FILE, 'r');
    if ($fd != NULL)
    {
        // 使用时，按文档和个人需求填写params
        $params = array(
            'notify-url' => NOTIFY_URL,
            'x-gmkerl-thumb' => '/format/png',
        );
        echo $client->write(IMAGE_SAVE_KEY, $fd, $params, true);
    }
    else
    {
        echo 'cannt open file:' . $file;
    }
}

/**
 * 异步音视频上传预处理
 * http://docs.upyun.com/cloud/av/
 */
function formVideoAsyncProcess()
{
    // 使用时，按文档和个人需求填写apps
    $apps = array(array(
        'name' => 'naga',
        'type' => 'video',
        'avopts' => '/s/128x96',
        'save_as' => VIDEO_SAVE_AS
    ));
    formAsyncPreProcess(VIDEO_FILE, VIDEO_SAVE_KEY, $apps);
}

/**
 * 文档转换上传预处理
 * http://docs.upyun.com/cloud/uconvert/
 */
function formDocAsyncConvert()
{
    // 使用时，按文档和个人需求填写apps
    $apps = array(array(
        'name' => 'uconvert',
        'save_as' => DOC_SAVE_AS
    ));
    formAsyncPreProcess(DOC_FILE, DOC_SAVE_KEY, $apps);
}

/**
 * 图片内容识别上传预处理
 * http://docs.upyun.com/ai/audit/
 */
function formImageAsyncAudit()
{
    // 使用时，按文档和个人需求填写apps
    $apps = array(array(
        'name' => 'imgaudit',
    ));
    formAsyncPreProcess(IMAGE_FILE, IMAGE_SAVE_KEY, $apps);
}

/**
 * 视频内容识别上传预处理
 * http://docs.upyun.com/ai/audit/
 */
function formVideoAsyncAudit()
{
    // 使用时，按文档和个人需求填写apps
    $apps = array(array(
        'name' => 'videoaudit',
    ));
    formAsyncPreProcess(VIDEO_FILE, VIDEO_SAVE_KEY, $apps);
}

/**
 * 接口调用
 */
formImageAsyncProcess();
formImageSyncProcess();
formVideoAsyncProcess();
formDocAsyncConvert();
formImageAsyncAudit();
formVideoAsyncAudit();

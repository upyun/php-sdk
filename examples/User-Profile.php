<?php
/**
 * 又拍云 PHP-SDK examle 用户配置文件
 * 测试与运行例子的时，用户需要根据自己的需求填写对应的配置，参数
 */

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * 用户操作员名
 */
define('USER_NAME', '');

/**
 * 用户服务名
 */
define('SERVICE', '');

/**
 * 用户密码
 */
define('PWD', '');

/**
 * 指定的通知URL
 */
define('NOTIFY_URL', '');

/**
 * 本地图片路径，适用于图片文件上传，预处理
 */
define('IMAGE_FILE', './sample/sample.jpg');

/**
 * 本地视频路径，适用于视频文件上传，预处理
 */
define('VIDEO_FILE', './sample/sample.mp4');

/**
 * 本地文档路径，包括PDF，PPT，WORD，EXCEL，适用于文档文件上传，预处理
 */
define('DOC_FILE', './sample/sample.pptx');

/**
 * 云存储中保存的图片文件路径，适用于图片相关上传，预处理，图片内容识别
 */
define('IMAGE_SAVE_KEY', '/save.png');

/**
 * 云存储中保存的视频文件路径，适用于视频相关上传，预处理，视频内容识别
 */
define('VIDEO_SAVE_KEY', '/save.mp4');

/**
 * 云存储中保存的文档文件路径，适用于文档相关上传，预处理，文档转换
 */
define('DOC_SAVE_KEY', '/save.pptx');

/**
 * 云存储中 save_as 参数指定的图片路径，适用于图片相关
 */
define('IMAGE_SAVE_AS', '/process/save.png');

/**
 * 云存储中 save_as 参数指定的视频路径，适用于视频相关
 */
define('VIDEO_SAVE_AS', '/process/save.mp4');

/**
 * 云存储中 save_as 参数指定的文档路径，适用于文档转换 
 */
define('DOC_SAVE_AS', '/process/save');

/**
 * 云存储中 save_as 参数指定的路径，适用于文件拉取服务 
 */
define('SAVE_AS', '/process/save.jpg');

/**
 * 云存储中 save_as 参数指定的压缩文件路径，适用于文件压缩，解压 
 */
define('COMPRESS_SAVE', '/process/save.zip');

/**
 * 云存储中目录，适用于文件解压 
 */
define('REMOTE_DIR', '/process');

/**
 * 文件URL，适用于文件拉取 
 */
define('URL', 'http://p07vpkunh.bkt.clouddn.com/aaaaa/image.png');

/**
 * RTMP源，适用于内容识别-直播
 */
define('RTMP_SOURCE', 'rtmp://live.hkstv.hk.lxdns.com/live/hks');
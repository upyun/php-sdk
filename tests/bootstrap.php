<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

define('USER_NAME', $config['user_name']);
define('PWD', $config['pwd']);
define('BUCKET', $config['bucket']);

define('PIC_PATH', $config['picture_path']);
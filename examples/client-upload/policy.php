<?php
require  __DIR__ . '/../../tests/bootstrap.php';

use Upyun\Config;
use Upyun\Signature;

$config = new Config(BUCKET, USER_NAME, PWD);
$config->setFormApiKey('Mv83tlocuzkmfKKUFbz2s04FzTw=');

$data['save-key'] = $_GET['save_path'];
$data['expiration'] = time() + 120;
$policy = Signature::getFormSignature($config, $data);
$policy['authorization'] = 'UPYUN ' . USER_NAME . ':' . $policy['signature'];
echo json_encode($policy);



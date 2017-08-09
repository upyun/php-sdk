# 又拍云 PHP SDK
[![Build Status](https://scrutinizer-ci.com/g/upyun/php-sdk/badges/build.png?b=master)](https://scrutinizer-ci.com/g/upyun/php-sdk/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/upyun/php-sdk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/upyun/php-sdk/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/upyun/php-sdk/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/upyun/php-sdk/?branch=master) 

又拍云存储PHP SDK，基于[又拍云存储 HTTP API 接口](http://docs.upyun.com/api/) 开发。SDK 包含了文件上传下载刷新等基本操作，以及图片、视频云处理等功能。

- [更新说明](#update-instructions)
- [使用说明](#use-instructions)
  - [安装](#install)
  - [使用](#usage)
- [贡献代码](#contribute)
- [社区](#community)
- [许可证](#license)

<a name="update-instructions"></a>
## 更新说明
#### 3.0.0

- 重写 API 接口，不兼容 2.x 版本
- 集合分块、刷新、视频预处理功能

#### 2.2.0

- 增加 composer 支持，特别感谢 [@totoleo](https://github.com/totoleo) 将 `upyun/sdk` 仓库源修改为 UPYUN 官方项目地址
- 移除不再推荐使用的 API:`rmDir deleteFile readDir getWritedFileInfo`)，建议使用推荐方法替代
- note: `2.1.0` 版本之前已经被 [@totoleo](https://github.com/totoleo) 使用

#### 2.0.0

- 使用1.0.x系列版本SDK的用户，注意原有部分方法已经不再推荐使用(`@deprecated`标注)，但是出于兼容考虑目前任然保留，建议更新升级程序使用新版SDK提供的方法。



<a name="use-instructions"></a>
## 使用说明

<a name="install"></a>
### 安装

#### PHP >= 5.5

1.使用 `composer` 安装

推荐配置 composer 使用国内全量镜像: https://pkg.phpcomposer.com/ 再安装

```
composer require upyun/sdk
```

2.如果不适应 `composer` 管理，可以直接下载[压缩包](https://github.com/upyun/php-sdk/releases)(注意需要下载 `php-sdk-版本号.zip` 格式的 zip 压缩包，不是 Source code 源码压缩包)，解压后，项目中添加如下代码：

```
require_once '/path/to/php-sdk/vendor/autoload.php';
```

<a name="usage"></a>
### 初始化

```php
require_once('vendor/autoload.php'); // 只针对使用 composer 安装
// require_once '/path/to/php-sdk/vendor/autoload.php'; // 针对压缩包安装

use Upyun\Upyun;
use Upyun\Config;
$serviceConfig = new Config('yourServiceName', 'yourOperatorName', 'yourOperatorPwd');
$client = new Upyun($serviceConfig);
```

详细文档见 [doc.md](doc.md)，以下为部分简单示例

#### 字符串写入又拍云服务器

```
$client->write('/save/path', 'file content');
```

#### 文件流写入又拍云服务器

```
$file = fopen('/local/path/file', 'r');
$client->write('/save/path', $file);
```

#### 上传图片并转换格式为 `png`，详见[上传作图](http://docs.upyun.com/cloud/image/#_2)

```
$file = fopen('/local/path/image.jpg', 'r');
$client->write('/save/image.png', $file, array('x-gmkerl-thumb' => '/format/png'));
```

#### 下载文件并保存到本地 

```
$saveLocal = fopen('/local/path/image.jpg', 'w');
// 第二个参数不传时，read 方法将直接返回文件内容
$client->read('/remote/server/image.png', $saveLocal);
```

<a name="contribute"></a>
## 贡献代码
 1. Fork
 2. 为新特性创建一个新的分支
 3. 发送一个 pull request 到 master 分支

<a name="community"></a>
## 社区

 - [问答社区](http://segmentfault.com/upyun)
 - [微博](http://weibo.com/upaiyun)

<a name="license"></a>
## 许可证

UPYUN PHP-SDK 基于 MIT 开源协议

<http://www.opensource.org/licenses/MIT>


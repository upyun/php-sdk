# 又拍云PHP SDK
![build](https://travis-ci.org/upyun/php-sdk.svg)

又拍云存储PHP SDK，基于 [又拍云存储HTTP REST API接口](http://docs.upyun.com/api/rest_api/) 开发。
- [更新说明](#update instructions)
- [使用说明](#use instructions)
  - [初始化UpYun](#init)
- [示例](#usage)
  - [上传文件](#upload file)
  - [上传图片](#upload img)
  - [下载文件](#download file)
  - [创建目录](#mkdir)
  - [删除目录或者文件](#delete)
  - [获取目录文件列表](#file list)
  - [获取文件信息](#file info)
  - [获取空间使用状况](#bucket info)
- [异常处理](#exception)
- [贡献代码](#contribute)
- [社区](#community)
- [许可证](#license)

<a name="update instructions"></a>
## 更新说明
使用1.0.x系列版本SDK的用户，注意原有部分方法已经不再推荐使用(`@deprecated`标注)，但是出于兼容考虑目前任然保留，建议更新升级程序使用新版SDK提供的方法。



<a name="use instructions"></a>
## 使用说明
下载`upyun.class.php`到项目目录

<a name="init"></a>
### 初始化UpYun
```php
require_once('upyun.class.php');
$upyun = new UpYun('bucketname', 'operator_name', 'operator_pwd');
```

参数 `bucketname` 为空间名称，`operator_name`、`operator_pwd` 为授权操作员的账号密码。

根据国内的网络情况，又拍云存储API目前提供了电信、联通网通、移动铁通三个接入点，在初始化的时候可以添加可选的第四个参数来指定API接入点。

```php
$upyun = new UpYun('bucketname', 'operator_name', 'operator_pwd', UpYun::ED_TELECOM);
```

接入点有四个值可选：

* `UpYun::ED_AUTO`    根据网络条件自动选择接入点
* `UpYun::ED_TELECOM` 电信接入点
* `UpYun::ED_CNC`     联通网通接入点
* `UpYun::ED_CTT`     移动铁通接入点

默认参数为自动选择API接入点。但是我们推荐根据服务器网络状况，手动设置合理的接入点已获取最佳的访问速度。

**超时时间设置**

在初始化UpYun上传时，可以选择设置上传请求超时时间（默认30s）:
```php
$upyun = new UpYun('bucketname', 'operator_name', 'operator_pwd', UpYun::ED_TELECOM, 600);
```

<a name="usage"></a>
## 示例

*示例代码中所有`bucketname`，`operator_name`，`operator_pwd`以及路径需要替换成实际环境的值，账户密码请注意保密*

<a name="upload file"></a>
### 上传文件

文件类空间可以上传任意形式的二进制文件

**1.直接读取整个文件内容:**
```php
$upyun->writeFile('/path/to/server/file.ext', 'your file content', true);
```

**2.文件流的方式上传，可降低内存占用:**
```php
$file_handler = fopen('demo.png', 'r');
$upyun->writeFile('/path/to/server/demo.png', $file_handler, true);
fclose($file_handler);
```
`writeFile()`第三个参数为可选，`true`表示自动创建相应目录，默认值为`false`。
文件空间上传成功后返回`true`。
如果上传失败，则会抛出异常。

<a name="upload img"></a>
### 上传图片
图片可以上传到图片类空间或文件类空间
* 图片空间上传的图片不能超过20M，图片`宽*高*帧数`不能超过`2亿`
* 文件空间上传的图片不能超过1G
*建议站点图片上传到图片空间，便于在请求图片时可以生成自定义版本图片*

**1.上传图片并创建缩略图:**

`writeFile()`方法第四个参数为数组类型可选参数，用来设置文件类型、缩略图处理。
```php
$opts = array(
	UpYun::X_GMKERL_THUMBNAIL => 'square' //创建缩略图,该参数仅适用于图片空间
);

$fh = fopen('demo.png', 'r');
$upyun->writeFile('/temp/upload_demo.png', $fh, true, $opts);
fclose($fh);
```
`writeFile()`方法第四个参数可以设置的值还包括：

* UpYun::CONTENT_TYPE
* UpYun::CONTENT_MD5
* UpYun::CONTENT_SECRET
* UpYun::X_GMKERL_THUMBNAIL
* UpYun::X_GMKERL_TYPE
* UpYun::X_GMKERL_VALUE
* UpYun::X_GMKERL_QUALITY
* UpYun::X_GMKERL_UNSHARP

参数的具体使用方法，请参考[标准API上传文件](http://docs.upyun.com/api/rest_api/#_4)

* 图片空间上传成功后会返回一维数组，包含了图片信息，示例如下:

```php
array(
  'x-upyun-width' => 2000,
  'x-upyun-height' => 1000,
  'x-upyun-frames' => 1
  'x-upyun-file-type' => "JPEG"
)
```
如果上传失败，则会抛出异常。

<a name="download file"></a>
### 下载文件

**1.直接读取文件内容:**
```php
$data = $upyun->readFile('/temp/upload_demo.png');
```

**2.使用文件流模式下载:**
```php
$fh = fopen('/tmp/demo.png', 'w');
$upyun->readFile('/temp/upload_demo.png', $fh);
fclose($fh);
```

直接获取文件时，返回文件内容，使用数据流形式获取时，成功返回`true`。
如果获取文件失败，则抛出异常。

<a name="mkdir"></a>
### 创建目录
```php
$upyun->makeDir('/demo/');
```
目录路径必须以斜杠 `/` 结尾，创建成功返回 `true`，否则抛出异常。

<a name="delete"></a>
### 删除目录或者文件
```php
$upyun->delete('/demo/'); // 删除目录
$upyun->delete('/demo/demo.png'); // 删除文件
```
删除成功返回`true`，否则抛出异常。注意删除目录时，`必须保证目录为空` ，否则也会抛出异常。

<a name="file list"></a>
### 获取目录文件列表
```php
$list = $upyun->getList('/demo/');
$file = $list[0];
echo $file['name'];	// 文件名
echo $file['type'];	// 类型（目录: folder; 文件: file）
echo $file['size'];	// 尺寸
echo $file['time'];	// 创建时间
```
获取目录文件以及子目录列表。需要获取根目录列表是，使用 `$upyun->getList('/')` ，或直接表用方法不传递参数。
目录获取失败则抛出异常。

<a name="file info"></a>
### 获取文件信息
```php
$result = $upyun->getFileInfo('/demo/demo.png');
echo $result['x-upyun-file-type']; // 文件类型
echo $result['x-upyun-file-size']; // 文件大小
echo $result['x-upyun-file-date']; // 创建日期
```
返回结果为一个数组。

<a name="bucket info"></a>
### 获取空间使用状况
```php
$upyun->getBucketUsage();	// 获取Bucket空间使用情况
```
返回的结果为空间使用量，单位 ***Byte***

<a name="exception"></a>
## 异常处理
当API请求发生错误时，SDK将抛出异常，具体错误代码请参考[标准API错误代码表](http://docs.upyun.com/api/rest_api/#rest-api)

根据返回HTTP CODE的不同，SDK将抛出以下异常：

* **UpYunAuthorizationException** 401，授权错误
* **UpYunForbiddenException** 403，权限错误
* **UpYunNotFoundException** 404，文件或目录不存在
* **UpYunNotAcceptableException** 406， 目录错误
* **UpYunServiceUnavailable** 503，系统错误

未包含在以上异常中的错误，将统一抛出 `UpYunException` 异常。

为了正确处理API请求中可能出现的异常，建议将API操作放在`try{...}catch(Exception
$e){…}` 块中，如下所示：

```php
try {
	$upyun->getFolderUsage('/demo/');
    //your code here

} catch(Exception $e) {
	echo $e->getCode();		// 错误代码
	echo $e->getMessage();	// 具体错误信息
}
```

<a name="contribute"></a>
## 贡献代码
 1. Fork
 2. 为新特性创建一个新的分支
 3. 发送一个 pull request 到 develop 分支

<a name="community"></a>
## 社区

 - [UPYUN问答社区](http://segmentfault.com/upyun)
 - [UPYUN微博](http://weibo.com/upaiyun)

<a name="license"></a>
## 许可证

UPYUN PHP-SDK基于 MIT 开源协议

<http://www.opensource.org/licenses/MIT>


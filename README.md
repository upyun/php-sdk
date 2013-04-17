# 又拍云PHP SDK

又拍云存储PHP SDK，基于 [又拍云存储HTTP REST API接口](http://wiki.upyun.com/index.php?title=HTTP_REST_API%E6%8E%A5%E5%8F%A3) 开发。

## 更新说明
使用1.0.x系列版本SDK的用户，注意原有部分方法已经不再推荐使用，但是出于兼容考虑目前任然保留，建议更新升级程序使用新版SDK提供的方法。

## 使用说明

### 初始化UpYun
````
require_once('upyun.class.php');
$upyun = new UpYun('bucketname', 'username', 'password');
````

参数 `bucketname` 为空间名称，`useranme`、`password` 为授权操作员的账号密码。

根据国内的网络情况，又拍云存储API目前提供了电信、联通网通、移动铁通三个接入点，在初始化的时候可以添加可选的第四个参数来指定API接入点。

````
$upyun = new UpYun('bucketname', 'username', 'password', UpYun::ED_TELECOM);
````

接入点有四个值可选：

* **UpYun::ED_AUTO** 根据网络条件自动选择接入点
* **UpYun::ED_TELECOM** 电信接入点
* **UpYun::ED_CNC** 联通网通接入点
* **UpYun::ED_CTT** 移动铁通接入点

默认参数为自动选择API接入点。但是我们推荐根据服务器网络状况，手动设置合理的接入点已获取最佳的访问速度。

### 上传文件

````
// 直接传递文件内容的形式上传
$upyun->writeFile('/temp/text_demo.txt', 'Hello World', True);

// 数据流方式上传，可降低内存占用
$fh = fopen('demo.png', 'r');
$upyun->writeFile('/temp/upload_demo.png', $fh, True);
fclose($fh);
````
第三个参数为可选。True 表示自动创建相应目录，默认值为False。

本方法还有一个数组类型的可选参数，用来设置文件类型、缩略图处理等参数。

````
$opts = array(
	UpYun::X_GMKERL_THUMBNAIL => 'square' // 缩略图版本，仅适用于图片空间
);

$fh = fopen('demo.png', 'r');
$upyun->writeFile('/temp/upload_demo.png', $fh, True, $opts);
fclose($fh);
````
该参数可以设置的值还包括：

* UpYun::CONTENT_TYPE
* UpYun::CONTENT_MD5
* UpYun::CONTENT_SECRET
* UpYun::X_GMKERL_THUMBNAIL
* UpYun::X_GMKERL_TYPE
* UpYun::X_GMKERL_VALUE
* UpYun::X_GMKERL_QUALITY
* UpYun::X_GMKERL_UNSHARP

参数的具体使用方法，请参考 [标准API上传文件](http://wiki.upyun.com/index.php?title=%E6%A0%87%E5%87%86API%E4%B8%8A%E4%BC%A0%E6%96%87%E4%BB%B6)

文件空间上传成功后返回`True`，图片空间上传成功后一数组形式返回图片信息：

````
array(
  'x-upyun-width' => 2000,
  'x-upyun-height' => 1000,
  'x-upyun-frames' => 1
  'x-upyun-file-type' => "JPEG"
)
````
如果上传失败，则会抛出异常。

### 下载文件
````
// 直接读取文件内容
$data = $upyun->readFile('/temp/upload_demo.png');

// 使用数据流模式下载，节省内存占用
$fh = fopen('/tmp/demo.png', 'w');
$upyun->readFile('/temp/upload_demo.png', $fh);
fclose($fh);
````

直接获取文件时，返回文件内容，使用数据流形式获取时，成功返回`True`。
如果获取文件失败，则抛出异常。

### 创建目录
````
$upyun->mkDir('/demo/');
````
目录路径必须以斜杠 `/` 结尾，创建成功返回 `True`，否则抛出异常。

### 删除目录或者文件
````
$upyun->delete('/demo/'); // 删除目录
$upyun->delete('/demo/demo.png'); // 删除文件
````
删除成功返回True，否则抛出异常。注意删除目录时，`必须保证目录为空` ，否则也会抛出异常。

### 获取目录文件列表
````
$list = $upyun->getList('/demo/');
$file = $list[0];
echo $file['name'];	// 文件名
echo $file['type'];	// 类型（目录: folder; 文件: file）
echo $file['size'];	// 尺寸
echo $file['time'];	// 创建时间
````
获取目录文件以及子目录列表。需要获取根目录列表是，使用 `$upyun->getList('/')` ，或直接表用方法不传递参数。
目录获取失败则抛出异常。

### 获取文件信息
````
$result = $upyun->getFileInfo('/demo/demo.png');
echo $result['x-upyun-file-type']; // 文件类型
echo $result['x-upyun-file-size']; // 文件大小
echo $result['x-upyun-file-date']; // 创建日期
````
返回结果为一个数组。

### 获取空间使用状况
````
$upyun->getFolderUsage();	// 获取Bucket空间使用情况
$upyun->getFolderUsage('/demo/'); 获取目录空间使用情况
````
返回的结果为空间使用量，单位 ***Byte***

## 异常处理
当API请求发生错误时，SDK将抛出异常，具体错误代码请参考 [标准API错误代码表](http://wiki.upyun.com/index.php?title=%E6%A0%87%E5%87%86API%E9%94%99%E8%AF%AF%E4%BB%A3%E7%A0%81%E8%A1%A8)

根据返回HTTP CODE的不同，SDK将抛出以下异常：

* **UpYunAuthorizationException** 401，授权错误
* **UpYunForbiddenException** 403，权限错误
* **UpYunNotFoundException** 404，文件或目录不存在
* **UpYunNotAcceptableException** 406， 目录错误
* **UpYunServiceUnavailable** 503，系统错误

未包含在以上异常中的错误，将统一抛出 `UpYunException` 异常。

为了真确处理API请求中可能出现的异常，建议将API操作放在`try{...}catch(Exception $e){…}` 块中

````
try{
	$upyun->getFolderUsage('/demo/');
	...
}
catch(Exception $e) {
	echo $e->getCode();		// 错误代码
	echo $e->getMessage();	// 具体错误信息
}
````

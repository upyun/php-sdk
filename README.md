# 又拍云PHP SDK

又拍云存储PHP SDK，基于 [又拍云存储HTTP REST API接口](http://wiki.upyun.com/index.php?title=HTTP_REST_API%E6%8E%A5%E5%8F%A3) 开发。

## 使用说明

### 初始化UpYun
> require_once('upyun.class.php');
>
> $upyun = new UpYun('bucketname', 'username', 'password');

参数 `bucketname` 为空间名称，`useranme`、`password` 为授权操作员的账号密码，不是又拍云的登陆账号密码。

根据国内的网络情况，又拍云存储API目前提供了电信、网通、铁通三个接入点，在初始化的时候可以添加可选的第四个参数来指定API接入点。
> $upyun = new UpYun('bucketname', 'username', 'password', UpYun::$ED_TELECOM);

接入点有四个值可选：

* **UpYun::$ED_AUTO** 根据网络条件自动选择接入点
* **UpYun::$ED_TELECOM** 电信接入点
* **UpYun::$ED_CNC** 网通接入点
* **UpYun::$ED_CTT** 铁通接入点

默认参数为自动选择API接入点。但是我们推荐根据服务器网络状况，手动选择合理的接入点已获取最佳的反问速度。
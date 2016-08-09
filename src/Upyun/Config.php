<?php
namespace Upyun;

/**
 * Class Config
 * 
 * @package Upyun
 */
class Config {
    /**
     * @var string: 服务名
     */
    public $bucketName;
    /**
     * @var string: 操作员名称
     */
    public $operatorName;
    /**
     * @var string: 操作员密码
     */
    public $operatorPassword;

    
    /**
     * @var string: 表单 API 秘钥，通过管理后台获取
     */
    private $formApiKey;
    
    /**
     * @var string: HTTP REST API 和 HTTP FORM  API 所使用的接口地址, 默认 ED_AUTO
     */
    static $restApiEndPoint;

    
    /**
     * 适合不同国内不同线路的接口地址
     * 关于国内不同线路选择的详细描述见: http://docs.upyun.com/api/
     */
    const ED_AUTO            = 'v0.api.upyun.com';
    const ED_TELECOM         = 'v1.api.upyun.com';
    const ED_CNC             = 'v2.api.upyun.com';
    const ED_CTT             = 'v3.api.upyun.com';

    /**
     * 分块上传接口地址
     */
    const ED_FORM            = 'm0.api.upyun.com';

    /**
     * 视频预处理接口地址
     */
    const ED_VIDEO           = 'p0.api.upyun.com';

    /**
     * 单个 URL 刷新接口地址
     */
    const ED_PURGE           = 'http://purge.upyun.com/purge/';

    public function __construct($bucketName, $operatorName, $operatorPassword) {
        $this->bucketName = $bucketName;
        $this->operatorName = $operatorName;
        $this->setOperatorPassword($operatorPassword);
        self::$restApiEndPoint = self::ED_AUTO;
    }
    
    public function setOperatorPassword($operatorPassword) {
        $this->operatorPassword = md5($operatorPassword); 
    }

    public function getFormApiKey() {
        if(! $this->formApiKey) {
            throw new \Exception('form api key is empty.');
        }
        
       return $this->formApiKey; 
    }

    public function setFormApiKey($key) {
        $this->formApiKey = $key;
    }

    /**
     * 根据文件路径,获取接口地址
     * @param $remoteFilePath
     *
     * @return string
     */
    public function getRestApiUrl($remotePath) {
        return "http://" . self::$restApiEndPoint . Util::pathJoin($this->bucketName, $remotePath);
    }
}

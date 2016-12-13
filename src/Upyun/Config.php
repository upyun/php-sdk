<?php
namespace Upyun;

/**
 * Class Config
 *
 * @package Upyun
 */
class Config {
    /**
     * @var string
     */
    public $bucketName;
    /**
     * @var string
     */
    public $operatorName;
    /**
     * @var string
     */
    public $operatorPassword;

    /**
     * @var bool: if true, use ssl
     */
    public $useSsl;

    /**
     * @var string: REST, use rest api upload file; BLOCK use multipart api upload file; AUTO, decide by file size
     */
    public $uploadType = 'AUTO';

    /**
     * @var int: if upload type is AUTO, when file size big than 10M will choose block upload, else use rest api upload
     */
    public $sizeBoundary = 10485760;
    /**
     * @var int: max block size 5M
     */
    public $maxBlockSize = 5242880;

    public $blockExpiration = 60;

    /**
     * @var int: request timeout seconds
     */
    public $timeout = 60;
    
    
    public $videoNotifyUrl;
    
    private $version = '3.0.0';



    /**
     * @var string
     */
    private $formApiKey;

    /**
     * @var string
     */
    static $restApiEndPoint;


    /**
     * different route type, detail see http://docs.upyun.com/api/
     */
    const ED_AUTO            = 'v0.api.upyun.com';
    const ED_TELECOM         = 'v1.api.upyun.com';
    const ED_CNC             = 'v2.api.upyun.com';
    const ED_CTT             = 'v3.api.upyun.com';

    /**
     * multipart api endpoint
     */
    const ED_FORM            = 'm0.api.upyun.com';

    /**
     * media api endpoint
     */
    const ED_VIDEO           = 'p0.api.upyun.com';

    /**
     * purge api endpoint
     */
    const ED_PURGE           = 'http://purge.upyun.com/purge/';

    public function __construct($bucketName, $operatorName, $operatorPassword) {
        $this->bucketName = $bucketName;
        $this->operatorName = $operatorName;
        $this->setOperatorPassword($operatorPassword);
        $this->useSsl          = false;
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
    
    public function getVersion() {
        return $this->version; 
    }
}

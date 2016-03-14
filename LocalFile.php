<?php
namespace Upyun;

class LocalFile {
    /**
     * @var string: 文件绝对路径
     */
    protected $realPath;
    /**
     * @var int:文件大小,单位Byte
     */
    protected $fileSize;
    /**
     * @var string:文件HASH
     */
    protected $md5FileHash;
    /**
     * @var resource:文件句柄
     */
    protected $handler;

    public function __construct($path) {
        $this->realPath = realpath($path);
        if(!($this->realPath && file_exists($this->realPath))) {
            throw new \Exception('local file not exists: ' . $path);
        }
        $this->fileSize = filesize($path);
        $this->md5FileHash = md5_file($this->realPath);
    }

    public function getFileSize() {
        return $this->fileSize;
    }

    public function getMd5FileHash() {
        return $this->md5FileHash;
    }

    public function getHandler() {
        if(is_resource($this->handler) === false) {
            $this->handler = fopen($this->realPath, 'rb');
        }
        return $this->handler;
    }

    public function closeHandler() {
        if(is_resource($this->handler)) {
            fclose($this->handler);
        }
    }


    /**
     * 读取文件块
     * @param $currentPosition: 文件当前读取位置
     * @param $endPosition: 文件读取结束位置
     * @param int $len: 每次读取的字节数
     * @return string
     */
    public function readBlock($currentPosition, $endPosition, $len = 8192) {
        $data = '';
        while($currentPosition < $endPosition) {
            if($currentPosition + $len > $endPosition) {
                $len = $endPosition - $currentPosition;
            }

            fseek($this->getHandler(), $currentPosition);
            $data .= fread($this->getHandler(), $len);
            $currentPosition = $currentPosition + $len;
        }
        return $data;
    }

    public function getRealPath() {
        return $this->realPath;
    }

    public function __destruct() {
        $this->closeHandler();
    }
}

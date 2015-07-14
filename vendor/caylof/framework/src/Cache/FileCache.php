<?php
namespace Caylof\Cache;

/**
 * 缓存文件类
 *
 * @package Caylof\Cache
 * @author caylof
 */
class FileCache {

    /**
     * 缓存文件存放目录（STORAGE_PATH 中的 caches 目录）
     *
     * @var string
     */
    const CACHE_DIR = 'caches';

    /**
     * 缓存文件后缀名
     *
     * @const string
     */
    const CACHE_SUFFIX = '.cache';

    /**
     * 缓存文件存储位置
     *
     * @var string
     */
    private $_cacheFile;

    /**
     * 缓存文件有效期（单位：秒）
     *
     * @var int
     */
    private $_timeout;

    /**
     * 构造函数
     *
     +----------------------------------------------------------------------------
       for example:

        $fc = new FileCache('views/test.index.index', 1800);

        该例子中缓存文件存放路径为：STORAGE_PATH.'/caches/views/test.index.index.cache'，
        有效期为半小时（1800秒）
     ----------------------------------------------------------------------------+
     *
     * @param string $cacheName 缓存文件相对于缓存目录中的相对路径（去掉后缀名）
     * @param int $timeout 缓存文件有效期（单位：秒）
     */
    public function __construct($cacheName, $timeout = 3600) {
        $cacheName = trim($cacheName, '/');
        $path = STORAGE_PATH.'/'.self::CACHE_DIR.'/'.$cacheName.self::CACHE_SUFFIX;
        $this->_cacheFile = preg_replace("/[\/\\\]+/", '/', $path);

        $this->_timeout = $timeout;
    }

    /**
     * 判断缓存文件是否过期（文件不存在或超过缓存时间都视为过期）
     *
     * @return bool 过期返回true，未过期返回false
     */
    public function isCacheTimeout() {
        if (file_exists($this->_cacheFile)) {
            $fmt = filemtime($this->_cacheFile);
            if ($fmt + $this->_timeout > time()) {
                return false;
            }
        }
        return true;
    }

    /**
     * 将需要缓存的内容写进缓存文件中
     *
     * @param string $content
     */
    public function writeCache($content) {
        // 创建缓存文件存放的目录
        $pathArr = explode('/', $this->_cacheFile);
        array_pop($pathArr);
        $cacheDir = join('/', $pathArr);
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // 将内容写入缓存文件
        touch($this->_cacheFile);
        $fp = fopen($this->_cacheFile, 'w');
        flock($fp, LOCK_EX);
        fputs($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 读取缓存文件内容
     *
     * @return string|bool 成功时返回文件内容，失败时返回false
     */
    public function readCache() {
        if (!$this->isCacheTimeout()) {
            $content = file($this->_cacheFile);
            return join('', $content);
        }
        return false;
    }

    /**
     * 直接 include 缓存文件
     */
    public function loadCache() {
        include($this->_cacheFile);
    }

    /**
     * 删除缓存文件
     *
     * @return bool
     */
    public function clearCache() {
        return @unlink($this->_cacheFile);
    }
}

<?php
namespace Caylof;

/**
 * 目录操作类
 *
 * @package Caylof
 * @author caylof
 */
class Folder {

    /**
     * 默认路径分隔符
     *
     * @var string
     */
    const DS = '/';

    /**
     * 统一目录路径的格式
     * 统一路径分隔符为默认分隔符，并在目录路径末尾加上默认分隔符
     *
     * @param string $path 目录路径
     * @return string 格式化后的目录路径
     */
    public static function path($path) {
        return rtrim(preg_replace("/[\/\\\]+/", self::DS, $path), self::DS) . self::DS;
    }

    /**
     * 建立目录
     *
     * @param string $path 要建立的目录路径
     * @param int $mode 模式
     * @return boolean
     */
    public static function create($path, $mode = 0777) {
        $path = self::path($path);
        if (file_exists($path) || $path == '') {
            return true;
        }
        return mkdir($path, $mode, true);
    }

    /**
     * 删除目录及其下的所有子目录和文件
     *
     * @param string $path 要删除的目录路径
     */
    public static function delete($path) {
        $path = self::path($path);
        /*if (file_exists($path)) {
            if ($handle = opendir($path)) {
                while ($filename = readdir($handle)) {
                    if ($filename != '.' && $filename != '..') {
                        $subFile = $path . $filename;
                        if (is_dir($subFile)) {
                            self::delete($subFile);
                        }
                        if (is_file($subFile)) {
                            unlink($subFile);
                        }
                    }
                }
                closedir($handle);
                rmdir($path);
            }
        }*/
        $items = glob($path . '*');
        foreach ($items as $item) {
            if (is_dir($item)) {
                self::delete($item);
            }
            if (is_file($item)) {
                unlink($item);
            }
        }
        rmdir($path);
    }

    /**
     * 拷贝目录及其下的子目录和文件到另一个目录
     *
     * @param string $src 源目录
     * @param string $to 目标目录
     */
    public static function copy($src, $to) {
        $src = self::path($src);
        $to = self::path($to);
        if (!file_exists($to)) {
            mkdir($to, 0777, true);
        }
        /*if ($handle = opendir($src)) {
            while ($filename = readdir($handle)) {
                if ($filename != '.' && $filename != '..') {
                    $subSrcFile = $src . $filename;
                    $subToFile = $to . $filename;

                    if (is_dir($subSrcFile)) {
                        self::copy($subSrcFile, $subToFile);
                    }
                    if (is_file($subSrcFile)) {
                        copy($subSrcFile, $subToFile);
                    }
                }
            }
            closedir($handle);
        }*/
        $items = glob($src . '*');
        foreach ($items as $item) {
            $subTo = $to . basename($item);
            if (is_dir($item)) {
                self::copy($item, $subTo);
            }
            if (is_file($item)) {
                copy($item, $subTo);
            }
        }
    }

    /**
     * 剪切目录及其下的子目录和文件到另一个目录
     *
     * @param string $src 源目录
     * @param string $to 目标目录
     */
    public static function move($src, $to) {
        self::copy($src, $to);
        self::delete($src);
    }

    /**
     * 计算一个目录的大小
     *
     * @param string $path 目录路径
     * @return int
     */
    public static function size($path) {
        $path = self::path($path);
        $size = 0;
        /*if ($handle = opendir($path)) {
            while ($filename = readdir($handle)) {
                if ($filename != '.' && $filename != '..') {
                    $subFile = $path . $filename;
                    if (is_dir($subFile)) {
                        $size += self::size($subFile);
                    }
                    if (is_file($subFile)) {
                        $size += filesize($subFile);
                    }
                }
            }
            closedir($handle);
        }*/
        $items = glob($path . '*');
        foreach ($items as $item) {
            if (is_dir($item)) {
                $size += self::size($item);
            }
            if (is_file($item)) {
                $size += filesize($item);
            }
        }
        return $size;
    }

    /**
     * 目录列表
     *
     * @param string $path 目录路径
     * @param int $pid 父目录ID
     * @param array $list 目录列表
     * @return array
     */
    public static function tree($path, $pid = 0, $list = array()) {
        static $id = 0;
        $path = self::path($path);
        $items = glob($path . '*');
        foreach ($items as $item) {
            if (is_dir($item)) {
                $id++;
                $list[$id] = array(
                    'id' => $id,
                    'pid' =>$pid,
                    'name' => basename($item),
                    'dir' => self::path($item)
                );
                $list = self::tree($item, $id, $list);
            }
        }
        return $list;
    }

    /**
     * 列出目录下的所有文件
     *
     * @param string $path 目录路径
     * @param string $suffix 文件后缀名
     * @param array $list 文件列表
     * @return array
     */
    public static function listFolder($path, $suffix = '', $list = array()) {
        $path = self::path($path);
        $items = glob($path . '*');
        foreach ($items as $item) {
            $fileSuffix = File::getSuffix($item);
            if (!$suffix || preg_match("/\.($suffix)/i", $fileSuffix)) {
                if (is_dir($item)) {
                    $list[] = self::path($item); // 列出子目录路径
                }
                if (is_file($item)) {
                    $list[] = $item;
                }
            }
        }
        return $list;
    }
}

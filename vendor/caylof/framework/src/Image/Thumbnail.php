<?php
namespace Caylof\Image;

/**
 * 缩略图处理类
 *
 * @package Caylof\Image
 * @author caylof
 *
 +---------------------------------------------------------------
   for example:

    // 创建一个最大宽度400最大高度400且允许按宽高比例缩放的处理类
    $tn = new Thumbnail(400, 400);

    // 加载图片文件
    $tn->loadFile('/path/to/image.jpg');

    // 加载图像数据
    $image = file_get_contents('/path/to/image.jpg');
    $tn->loadData($image, 'image/jpeg');

    // 生成缩略图文件
    $tn->buildThumb('path/to/image-thumb.jpg');

    // 直接在浏览器输出缩略图
    header('Content-type: ' . $tn->getMime());
    $tn->buildThumb();
 ---------------------------------------------------------------+
 *
 */
class Thumbnail {

    /**
     * 缩略图的最大像素宽度
     *
     * @var int
     */
    private $_maxWidth;

    /**
     * 缩略图的最大像素高度
     *
     * @var int
     */
    private $_maxHeight;

    /**
     * 是否按比例将图像缩小为缩略图（处理较大图像）
     *
     * @var bool
     */
    private $_scale;

    /**
     * 是否按比例将图像拉伸为缩略图（处理太小图像）
     *
     * @var bool
     */
    private $_inflate;

    /**
     * 能够处理的MIME类型
     *
     * @var array
     */
    private $_types;

    /**
     * 加载MIME类型的图像的函数
     *
     * @var array
     */
    private $_imgLoaders;

    /**
     * 创建MIME类型的图像的函数
     *
     * @var array
     */
    private $_imgCreators;

    /**
     * 源图片文件
     *
     * @var resource
     */
    private $_source;

    /**
     * 源图片文件宽度
     *
     * @var int
     */
    private $_sourceWidth;

    /**
     * 源图片文件高度
     *
     * @var int
     */
    private $_sourceHeight;

    /**
     * 源图片文件mime类型
     *
     * @var string
     */
    private $_sourceMime;

    /**
     * 缩略图片文件
     *
     * @var resource
     */
    private $_thumb;

    /**
     * 缩略图片文件宽度
     *
     * @var int
     */
    private $_thumbWidth;

    /**
     * 缩略图片文件高度
     *
     * @var int
     */
    private $_thumbHeight;

    /**
     * 构造函数
     *
     * @param int $maxWidth 要生成缩略图的最大宽度
     * @param int $maxHeight 要生成缩略图的最大高度
     * @param bool $scale 是否按宽高比例生成缩略图
     * @param bool $inflate 对源图片比要生成的缩略图片小的情况下是否将源图片进行放大
     */
    public function __construct($maxWidth, $maxHeight, $scale = true, $inflate = true) {
        $this->_maxWidth = $maxWidth;
        $this->_maxHeight = $maxHeight;
        $this->_scale = $scale;
        $this->_inflate = $inflate;

        // 能够支持的图片mime类型
        $this->_types = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');

        // 加载对应mime类型的图片函数
        $this->_imgLoaders = array(
            'image/gif' => 'imagecreatefromgif',
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/pjpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/x-png' => 'imagecreatefrompng'
        );

        // 生成对应mime类型的图片函数
        $this->_imgCreators = array(
            'image/gif' => 'imagegif',
            'image/jpeg' => 'imagejpeg',
            'image/pjpeg' => 'imagejpeg',
            'image/png' => 'imagepng',
            'image/x-png' => 'imagepng'
        );
    }

    /**
     * 加载源图片（图片路径）
     *
     * @param sting $image 要加载的源图片的文件路径
     * @throw Exception 文件不存在或文件mime类型不支持时皆抛出例外
     * @return bool 加载成功返回true
     */
    public function loadFile($image) {
        if (!$dims = @getimagesize($image)) {
            throw new ThumbnailException('Could not find image: ' . $image);
        }

        if (in_array($dims['mime'], $this->_types)) {
            $loader = $this->_imgLoaders[$dims['mime']];
            $this->_source = $loader($image);
            $this->_sourceWidth = $dims[0];
            $this->_sourceHeight = $dims[1];
            $this->_sourceMime = $dims['mime'];
            $this->_initThumb();
            return true;
        } else {
            throw new ThumbnailException('Image MIME type ' . $dims['mime'] . ' not supported');
        }
    }

    /**
     * 加载图片数据
     *
     * @param string $image 图片数据
     * @param stirng $mime 图片mime类型
     * @throw Exception 图片加载失败或不支持mime类型时抛出例外
     * @return bool 加载成功时返回true
     */
    public function loadData($image, $mime) {
        if (in_array($mime, $this->_types)) {
            if ($this->_source = @imagecreatefromstring($image)) {
                $this->_sourceWidth = imagesx($this->_source);
                $this->_sourceHeight = imagesy($this->_source);
                $this->_sourceMime = $mime;
                $this->_initThumb();
                return true;
            } else {
                throw new ThumbnailException('Could not load image from string');
            }
        } else {
            throw new ThumbnailException('Image MIME type ' . $mime . ' not supported');
        }
    }

    /**
     * 生成缩略图
     *
     * @param string $file 生成缩略图的存放路径
     * @return bool 生成成功时返回true，否则返回false
     */
    public function buildThumb($file = null) {
        $creator = $this->_imgCreators[$this->_sourceMime];
        if (isset($file)) {
            return $creator($this->_thumb, $file);
        } else {
            return $creator($this->_thumb);
        }
    }

    /**
     * 获取图片的mime类型
     *
     * @return string
     */
    public function getMime() {
        return $this->_sourceMime;
    }

    /**
     * 获取缩略图的宽度
     *
     * @return int
     */
    public function getThumbWidth() {
        return $this->_thumbWidth;
    }

    /**
     * 获取缩略图的高度
     *
     * @return int
     */
    public function getThumbHeight() {
        return $this->_thumbHeight;
    }

    /**
     * 处理图片缩放
     */
    private function _initThumb() {
        // 处理缩放比例
        $this->_thumbWidth = $this->_maxWidth;
        $this->_thumbHeight = $this->_maxHeight;
        if ($this->_scale) {
            if ($this->_sourceWidth > $this->_sourceHeight) {
                $this->_thumbWidth = $this->_maxWidth;
                $this->_thumbHeight = floor(
                    $this->_sourceHeight / $this->_sourceWidth * $this->_maxHeight
                );
            }
            if ($this->_sourceWidth < $this->_sourceHeight) {
                $this->_thumbHeight = $this->_maxHeight;
                $this->_thumbWidth = floor(
                    $this->_sourceWidth / $this->_sourceHeight * $this->_maxWidth
                );
            }
        }

        // 创建一个空白缩略图
        $this->_thumb = imagecreatetruecolor($this->_thumbWidth, $this->_thumbHeight);

        // 将源图像复制为缩略图像
        if ($this->_sourceWidth <= $this->_maxWidth && $this->_sourceHeight <= $this->_maxHeight && $this->_inflate == false) {
            $this->_thumb = $this->_source;
        } else {
            imagecopyresampled(
                $this->_thumb, $this->_source, 0, 0, 0, 0,
                $this->_thumbWidth, $this->_thumbHeight,
                $this->_sourceWidth, $this->_sourceHeight
            );
        }
    }

    /**
     * 释放图片资源
     */
    public function __destruct() {
        imagedestroy($this->_source);
        imagedestroy($this->_thumb);
    }
}

<?php
namespace Caylof\Image;

/**
 * 为图片添加水印
 *
 * @package Caylof\Image
 * @author caylof
 *
 +-----------------------------------------------------------------
   for example:

    // 创建实例
    $wm = new Watermark();

    // 加载源图片文件和水印图片
    $wm->loadFile('/path/to/source.jpg', '/path/to/water.png');

    // 直接输出图片
    header('Content-type: ' . $wm->getMime());
    $wm->buildWatermark();
    // 水印位置在源图片右上角
    $wm->buildWatermark(null, 2);

    // 保存添加水印后的图片
    $wm->buildWatermark('/path/to/addwm.jpg');
 -----------------------------------------------------------------+
 *
 */
class Watermark {

    /**
     * 源图片
     *
     * @var resource
     */
    private $_source;

    /**
     * 水印图片
     *
     * @var resource
     */
    private $_watermark;

    /**
     * 源图片mime类型
     *
     * @var string
     */
    private $_sourceMime;

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
     * 构造函数
     */
    public function __construct() {
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
     * 加载源图片文件和水印图片文件
     *
     * @param string $sourceFile 源图片文件路径
     * @param stirng $watermarkFile 水印图片文件路径
     */
    public function loadFile($sourceFile, $watermarkFile) {
        $this->_sourceMime = $this->_loadMime($sourceFile);
        $loader = $this->_imgLoaders[$this->_sourceMime];
        $this->_source = $loader($sourceFile);

        $loader = $this->_imgLoaders[$this->_loadMime($watermarkFile)];
        $this->_watermark = $loader($watermarkFile);
    }

    /**
     * 生成加水印后图片文件
     *
     * @param string $file 生成后的图像存放路径（null表示不生成新图像）
     * @pos int 水印位置（1到4，1：左上角；2：右上角；3：左下角；4：右下角）
     * @pct int 水印透明度（0到100，0表示不生成水印，100表示完全不透明）
     * @return bool 成功返回true，否则返回false
     */
    public function buildWatermark($file = null, $pos = 4, $pct = 40) {
        $iWidth = imagesx($this->_source);
        $iHeight = imagesy($this->_source);
        $wmWidth = imagesx($this->_watermark);
        $wmHeight = imagesy($this->_watermark);

        switch ($pos) {
            case 1:
                $xPos = 0;
                $yPos = 0;
                break;
            case 2:
                $xPos = $iWidth - $wmWidth;
                $yPos = 0;
                break;
            case 3:
                $xPos = 0;
                $yPos = $iHeight - $wmHeight;
                break;
            case 4:
                $xPos = $iWidth - $wmWidth;
                $yPos = $iHeight - $wmHeight;
                break;
            default:
                $xPos = $iWidth - $wmWidth;
                $yPos = $iHeight - $wmHeight;
        }

        imagecopymerge($this->_source, $this->_watermark, $xPos, $yPos, 0, 0, $wmWidth, $wmHeight, $pct);

        $creator = $this->_imgCreators[$this->_sourceMime];
        if (isset($file)) {
            return $creator($this->_source, $file);
        } else {
            return $creator($this->_source);
        }
    }

    /**
     * 获取源图片的mime类型
     *
     * @return string
     */
    public function getMime() {
        return $this->_sourceMime;
    }

    /**
     * 获取图片的mime类型
     *
     * @param string $image 图片路径
     * @throw Exception 图片不存在或者为不支持的mime类型时抛出例外
     * @return string
     */
    private function _loadMime($image) {
        if (!$dims = @getimagesize($image)) {
            throw new WatermarkException('Could not find image: ' . $image);
        }

        if (in_array($dims['mime'], $this->_types)) {
            return $dims['mime'];
        } else {
            throw new WatermarkException('Image MIME type ' . $dims['mime'] . ' not supported');
        }
    }

    /**
     * 释放图片资源
     */
    public function __destruct() {
        imagedestroy($this->_source);
        imagedestroy($this->_watermark);
    }
}

<?php
namespace Caylof\Image;

/**
 * 验证码生成类
 *
 * @package Caylof\Image
 * @author caylof
 *
 +-------------------------------------------------------------------------
   for example:

    // 验证码图片
    <img src="/path/to/createAuthCode">

    // 系统url路径"/path/to/createAuthCode"中应写入如下类似代码
    // 注意要将code写进session中以便进行后台验证
    $ac = new AuthCode();
    $_SESSION['authCode'] = $ac->buildCharCode();
 -------------------------------------------------------------------------+
 *
 */
class AuthCode {

    /**
     * 生成字符串验证码
     *
     * @param int $len 验证码字符长度
     * @param int $width 验证码图片宽度
     * @param int $height 验证码图片高度
     * @return string
     */
    public function buildCharCode($len = 4, $width = 60, $height = 20) {

        // 去除“0、1、O、l”这些混淆不清的字符
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $strLen = strlen($str);
        $code = '';
        for ($i = 0; $i < $len; ++$i) {
            $code .= $str[mt_rand(0, $strLen-1)];
        }

        //创建图片，定义颜色值
        $im = imagecreate($width, $height);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        $gray = imagecolorallocate($im, 118, 151, 199);
        $bgcolor = imagecolorallocate($im, 235, 236, 237);

        //画背景
        imagefilledrectangle($im, 0, 0, $width, $height, $bgcolor);
        //画边框
        imagerectangle($im, 0, 0, $width-1, $height-1, $gray);

        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; ++$i) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $black);
        }
        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = mt_rand(3, 8);
        for ($i = 0; $i < $len; ++$i) {
            $strpos = mt_rand(1, 6);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += mt_rand(8, 14);
        }

        // 直接输出图片
        header('Content-type: image/png');
        imagepng($im);

        // 销毁图片资源
        imagedestroy($im);

        // 返回验证码（用于SESSION接收来进行后台验证）
        return $code;
    }

    /**
     * 生成简单的加法计算式验证码
     *
     * @param int $width 验证码图像宽度
     * @param int $height 验证码图像高度
     */
    public function buildMathCode($width = 100, $height = 24) {
        $im = imagecreate($width, $height);

        $red = imagecolorallocate($im, 255, 0, 0);
        $white = imagecolorallocate($im, 255, 255, 255);
        $gray = imagecolorallocate($im, 118, 151, 199);
        $black = imagecolorallocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));

        //画背景
        imagefilledrectangle($im, 0, 0, 100, 24, $black);
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; ++$i) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $gray);
        }

        $num1 = mt_rand(1, 49);
        $num2 = mt_rand(1, 49);

        imagestring($im, 5, 5, 4, $num1, $red);
        imagestring($im, 5, 30, 3, "+", $red);
        imagestring($im, 5, 45, 4, $num2, $red);
        imagestring($im, 5, 70, 3, "=", $red);
        imagestring($im, 5, 80, 2, "?", $white);

        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);

        return $num1 + $num2;
    }
}

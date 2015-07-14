<?php
namespace Caylof\Filtrator;

/**
 * 最简单过滤器
 * 将字符串生成过滤器
 *
 * @package Caylof\Filtraotr
 * @author caylof
 */
class SimpleFiltrator implements IFiltrator {

    private $_input;

    /**
     * 构造函数
     *
     * @param string $input 要生成过滤器的字符串
     */
    public function __construct($input) {
        $this->_input = $input;
    }

    /**
     * 过滤方法
     * 此处不作过滤直接输出字符串
     */
    public function filter() {
        return $this->_input;
    }
}

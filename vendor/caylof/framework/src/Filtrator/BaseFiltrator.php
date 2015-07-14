<?php
namespace Caylof\Filtrator;

/**
 * 应用过滤器基类
 * 除简单过滤器（将字符串生成过滤器）外的过滤器应该继承该类
 *
 * @package Caylof\Filtraotr
 * @author caylof
 */
abstract class BaseFiltrator implements IFiltrator {

    /**
     * 内部过滤器
     *
     * @var \Caylof\Filtrator\IFiltrator
     */
    protected $filtrator;

    /**
     * 构造函数
     *
     * @param \Caylof\Filtrator\IFiltrator $filtrator 内部过滤器
     */
    public function __construct(IFiltrator $filtrator) {
        $this->filtrator = $filtrator;
    }
}

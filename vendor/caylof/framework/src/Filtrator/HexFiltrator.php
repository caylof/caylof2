<?php
namespace Caylof\Filtrator;

/**
 * 过滤十六进制字符
 *
 * @package Caylof\Filtraotr
 * @author caylof
 */
class HexFiltrator extends BaseFiltrator {

    public function filter() {
        return preg_replace(
            "![\][xX]([A-Fa-f0-9]{1,3})!",
            "",
            $this->filtrator->filter()
        );
    }

}

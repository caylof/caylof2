<?php
namespace Caylof\Mvc;

/**
 * 视图处理类
 *
 * @package Caylof\Mvc
 * @author caylof
 */
class View {

    /**
     * 模板文件存放目录（APP_PATH 中的 views 目录）
     *
     * @const string
     */
    const VIEWS_DIR = 'views';

    /**
     * 模板文件后缀名
     *
     * @const string
     */
    const TEMPLATE_SUFFIX = '.tpl.php';
    
    /**
     * 模板文件中的PHP变量集
     *
     * @var array
     */
    private $_vars = array();

    /**
     * 加载到当前视图中的模板文件
     *
     * @var string
     */
    private $_template = '';

    /**
     * 构造函数
     * 初始化模板文件
     *
     +-----------------------------------------------------------------------
       for example:
       $template = 'default/index/index';
       对应模板文件所在目录中的default/index/index.tpl.php文件
     -----------------------------------------------------------------------+
     *
     * @param string $template 模板文件在模板目录中的相对路径
     */
    public function __construct($template) {
        $template = trim($template, '/');
        if ($template == '') {
            throw new Exception('View create failed: path of template could not be empty');
        }
        $this->_template = $template;
    }

    /**
     * 加载子模板文件
     * 该方法可以在模板文件中使用，作用相当于加载子模块文件
     *
     +-----------------------------------------------------------------------
       for example:
       在模板文件default/index/index.tpl.php中嵌入代码$this->load('x/x/x');
     -----------------------------------------------------------------------+
     *
     * @param string $template 模板文件在模板目录中的相对位置
     */
    public function load($template = '') {
        $template = empty($template) ? $this->_template : $template;
        $template = trim($template, '/');
        extract($this->_vars, EXTR_SKIP);
        include(APP_PATH.'/'.self::VIEWS_DIR.'/'.$template.self::TEMPLATE_SUFFIX);
    }

    /**
     * 为视图模板文件中的PHP变量分配值
     * 如果参数$k是一个字符串，它将对应模板文件中的一个PHP变量，此时参数$v则是为$k分配的值；
     * 另一种情况，参数$v为一个数组，那么数组中的KEY对应模板文件中的一个PHP变量，数组中的VALUE为对应分配的值
     *
     +---------------------------------------------------------------------------------------
       for example:
       assign('id', 1)，这种情况表示模板文件中$id = 1;
       assign(array('name' => 'cctv', 'age' => 22))，模板中$name = 'cctv'; $age = 22;
     ---------------------------------------------------------------------------------------+
     *
     * @param string|Array $k
     * @param mixed $v
     * @return \Caylof\View 本类实例对象（以方便链式操作）
     */
    public function assign($k, $v = null) {
        if (is_array($k)) {
            foreach($k as $key => $val) {
                $this->_vars[$key] = $val;
            }
        } else {
            $this->_vars[$k] = $v;
        }
        return $this;
    }

    /**
     * 输出模板渲染后的内容（用于输出视图, 该方法应在assign方法后使用）
     */
    public function output() {
        ob_start();
        $this->load();
        ob_end_flush();
    }

    /**
     * 获取渲染后的视图内容（该方法应在assign方法后使用）
     *
     * @return string
     */
    public function render() {
        ob_start();
        $this->load();
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}

<?php
namespace Caylof;

/**
 * 路由请求类
 *
 * @package caylof
 * @author caylof
 */
class Router {

    /**
     * 本类实例对象
     *
     * @access private
     * @var \Caylof\Router 存储本类的一个实例，以实现单例模式
     */
    private static $instance;

    /**
     * 路由存储列表
     *
     * @access private
     * @var array
     */
    private $routerList;

    /**
     * 本类不允许被外界实例化，以实现单例模式
     */
    private function __construct() {
        
    }

    /**
     * 获取本类的一个实例
     *
     * @return \Caylof\Router
     */
    public static function getInstance() {
        if (!(static::$instance instanceof static)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * 添加路由请求
     *
     * @param string $type 请求类型（“GET”, “POST”）
     * @param string $route 路由路径
     * @param string|\Closure $todo 形式如“controller@method”的字符串或者“闭包”
     * @rule string|array $rule 路由路径中点位符规则
     */
    public function add($type, $route, $todo, $rule = null) {
        $patternDefault = '#\{(\w+?)\}#';
        $repalceDefault = '(\w+)';
        $pattern        = $patternDefault;
        $replace        = isset($rule) ? $rule : $repalceDefault;
        $route          = '/'.trim($route, '/ ');
        $this->checkRouteTodo($todo);
        if (is_array($rule)) {
            $pattern    = [];
            foreach (array_keys($rule) as $placeholder) {
                array_push($pattern, '#\{('.$placeholder.')\}#');
            }
            $replace    = array_values($rule);
        }
        $reg = preg_replace($pattern, $replace, $route);
        $reg = '#^'.preg_replace($patternDefault, $repalceDefault, $reg).'$#';
        $this->routerList[$type][md5($reg)] = [
            'route'  => $route,
            'reg'    => $reg,
            'todo'   => $todo
        ];
        return $this;
    }

    /**
     * 添加“GET”类型的请求
     */
    public function get($route, $todo, $rule = null) {
        return $this->add('GET', $route, $todo, $rule);
    }

    /**
     * 添加“POST”类型的请求
     */
    public function post($route, $todo, $rule = null) {
        return $this->add('POST', $route, $todo, $rule);
    }

    public function all($route, $todo, $rule = null) {
        $this->add('GET', $route, $todo, $rule);
        return $this->add('POST', $route, $todo, $rule);
    }

    /**
     * 将“请求”分解到相应“路由”
     */
    public function route() {
        $path   = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $uri    = parse_url($path);
        $uri    = '/'.trim($uri['path'], '/ ');
        $uri    = urldecode($uri);
        $type   = strtoupper($_SERVER['REQUEST_METHOD']);

        $ret    = $this->find($uri, $type);
        $router = $ret['router'];
        $params = $ret['params'];
        $todo   = $router['todo'];
        $this->dispatch($todo, $params);
    }

    /**
     * 根据“请求”查找相应“路由”
     *
     * @param string $uri 请求uri
     * @param string $type 请求类型（“GET”或者“POST”）
     * @return array('router' => '路由', 'params' => '请求参数')
     */
    public function find($uri, $type) {
        foreach ($this->routerList[$type] as $router) {
            if (preg_match($router['reg'], $uri, $matches)) {
                return [
                    'router' => $router,
                    'params' => array_slice($matches, 1)
                ];
            }
        }
        throw new Exception\NotFound('Router not found by the "'.$type.'" request "'.$uri.'"');
    }

    /**
     * 调用“路由处理方法”
     *
     * @param string|\Closure $todo 形式如“controller@method”的字符串或者“闭包”
     * @param array $params 方法$todo所需的参数
     */
    public function dispatch($todo, array $params) {
        if ($todo instanceof \Closure) {
            call_user_func_array($todo, $params);
        } else {
            $splits     = explode('@', $todo);
            $className  = array_shift($splits);
            $methodName = array_shift($splits);

            if (!class_exists($className)) {
                throw new Exception\NotFound('class "'.$className.'" not exist');
            }

            $rc = new \ReflectionClass($className);

            if (!$rc->hasMethod($methodName )) {
                throw new Exception\NotFound('method "'.$methodName.'" not exist in the class "'.$className.'"');
            }

            $contro = $rc->newInstance();
            $contro->setDI(DI::getInstance());
            $method = $rc->getMethod($methodName);

            if (count($params)) {
                $method->invokeArgs($contro, $params);
            } else {
                $method->invoke($contro);
            }
        }
    }

    /**
     * 判断“路由处理方法”格式是否正确
     * 字符串中包含“@”则简单认为格式正确
     */
    private function checkRouteTodo($todo) {
        if (is_string($todo) && (strpos($todo, '@') === false)) {
            throw new Exception\UnexpectedValue('"'.$todo.'"'." expect have \'@\' to split controller and method, but not have");
        }
    }

    /**
     * 本类不允许克隆，用于实现单例模式
     */
    private function __clone() {
        
    }
}

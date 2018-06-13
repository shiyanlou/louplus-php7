<?php

namespace core;

use dispatcher\Container;

class Router extends Container
{
    public $method;
    public $uri;
    public $path;
    public $controller;
    public $action;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->path = $_SERVER['PATH_INFO'] ?? '/';
        $this->uri = $_SERVER['REQUEST_URI'];

        require APP_PATH. '/app/routes.php';
    }

    public function __call($method,$args) {
        if (empty($args[1])) {
            Throw new \Exception('Too few args in Router::method()');
        }
        $method = strtoupper($method);
        if ($this->method === $method && $this->path === $args[0]) {
            //执行闭包函数
            if (is_object($args[1])) {
                call_user_func($args[1]);
                exit;
            }
            
            list($this->controller, $this->action) = explode('@',$args[1]);

            //重定向控制器或路由
            $this->path = '/'.strtolower(str_replace(['Controller','get','@'],['','','/'],$args[1]));
            $this->uri = str_replace($args[0],$this->path,$this->uri);
        }
    }

    /**
     * 执行解析
     *
     */
    protected function start()
    {
        $route = Config::get('default.route') ?? 'querystring';

        $path = explode('/',trim($this->path,'/'));
        
        //controller
        $controller = $this->controller ?? $this->getController($path[0]);

        //action
        $action = $this->action ?? $this->getAction($path[1]);

        //args
        $args = call_user_func_array([$this,$route],[$this->uri]);
        
        return ['controller'=>$controller,'action'=>$action,'args'=>$args];
    }

    private function getController($controller = null)
    {
        !empty($controller) or $controller = Config::get('default.controller','index');
        return ucfirst($controller).'Controller';
    }

    private function getAction($action = null)
    {
        return strtolower($this->method).ucfirst($action ?? Config::get('default.action','index'));
    }

    /**
     * 查询字符串解析
     *
     */
    private function querystring($url)
    {
        $urls = explode('?', $url);
        
        if (empty($urls[1])) {
            return [];
        }
        $param_arr = [];
        $param_tmp = explode('&', $urls[1]);
        if (empty($param_tmp)) {
            return [];
        }
        foreach ($param_tmp as $param) {
            if (strpos($param, '=')) {
                list($key,$value) = explode('=', $param);
                //变量名是否复合规则
                if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                    $param_arr[$key] = $value;
                }
            }
        }
        return $param_arr;
    }
    /**
     * 路径 url 解析
     *
     */
    private function restful($url)
    {
        $path = explode('/', trim(explode('?', $url)[0], '/'));
        $params = [];
        $i = 2;
        while (1) {
            if (!isset($path[$i])) {
                break;
            }
            $params[$path[$i]] = $path[$i+1] ?? '';
            $i = $i+2;
        }
        return $params;
    }
}

<?php

namespace core;

use dispatcher\Container;

class Application extends Container
{
    public $plugins;
    public $boot;

    /**
     * 初始化Bootstrapp
     *
     */
    public function __construct()
    {
        $this->boot = \app\Bootstrap::register();
    }

    /**
     * 加载启动项
     * 执行插件
     * 路由解析
     * 调用控制器方法
     * 输出内容
     */
    public function run()
    {
        //该类中所有以init开头的方法都会被调用
        $funcs = array_filter(get_class_methods($this->boot),[$this,'getBootFuncs']);
        foreach($funcs as $func) {
            \app\Bootstrap::call($func);
        }

        //路由前插件执行
        if (!empty($this->plugins)) {
            foreach($this->plugins as $plugin) {
                //$plugin::call('routerStartup');
                $plugin->routerStartup();
            }
        }

        //路由解析
        $router = Router::start();
        $controller = "controller\\".$router['controller'];
        $action = $router['action'];
        $args = $router['args'];
        $this->controller = $router['controller'];
        $this->action = $router['action'];
        $this->args = $router['args'];

        //路由解析后插件执行
        if (!empty($this->plugins)) {
            foreach($this->plugins as $plugin) {
                $plugin->routerShutdown();
            }
        }
        print_r($controller::call($action,$args));
    }

    private function getBootFuncs($func)
    {
        return 0 === strpos($func, 'init') ? 1 : 0;
    }
    public function __set($key, $value)
    {
        $this->request->$key = $value;
    }

    public function plugin($plugin)
    {
        $this->plugins[] = $plugin;
    }
}

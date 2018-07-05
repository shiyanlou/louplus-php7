<?php

namespace test;

define('APP_PATH',dirname(__DIR__));

use PHPUnit\Framework\TestCase;
use dispatcher\Container;

class DispatcherTest extends TestCase
{
    private static $obj;

    public static function setUpBeforeClass()
    {
	self::$obj = new Say();
    }

    public function testSayHi()
    {
	$this->assertEquals('Hi', self::$obj->hi());
    }
 
    public function testSayHello()
    {
	$this->assertEquals('Hello', Foo::call('sayHello', 'Say'));
    }
	
    public static function setDownAfterClass()
    {
	self::$obj = null;
    }

}
class Foo extends Container
{
    protected function sayHi(Say $say)
    {
        return $say->hi();
    }
    public function sayHello(Say $say)
    {
        return $say->hello();
    }
}
class Say extends Container
{
    public function hi()
    {
        return 'Hi';
    }
    public function hello()
    {
        return 'Hello';
    }
}

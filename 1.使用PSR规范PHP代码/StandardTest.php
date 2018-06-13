<?php

namespace Vendor\Shiyanlou;

use SayClass;

class StandardTest
{
    const ENV = 1;
    
    public function sayHello()
    {
        echo 'Hello';
    }

    public function sayHi()
    {
        echo 'Hi';
    }

    public function sayGoodBye($language)
    {
        if ($language === 'zh') {
            echo "拜拜";
        } else {
            echo "Bye";
        }
    }

    public static function sayYes($a, $b)
    {
        if ($a === $b) {
            SayClass::yes();
        } else {
            SayClass::no();
        }
    }
}

<?php

namespace app;

use core\Application;
use plugin\UserPlugin;
use plugin\AdminPlugin;
use dispatcher\Container;

class Bootstrap extends Container
{
    public function initRegisterPlugin(Application $app, UserPlugin $user, AdminPlugin $admin)
    {
        $app->plugin($user);
        $app->plugin($admin);
    }
}

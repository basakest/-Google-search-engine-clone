<?php

namespace App\Controllers\Admin;

class Users extends \Core\Controller
{
    protected function indexAction()
    {
        echo "Hello, I'm the index action of Users controller<br />";
    }

    protected function before()
    {
        echo 'before<br />';
        return true;
    }

    protected function after()
    {
        echo 'after<br />';
    }
}
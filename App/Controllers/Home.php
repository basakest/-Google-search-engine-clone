<?php

namespace App\Controllers;
use Core\View;

class Home extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Home/index.html');
    }

    public function searchAction()
    {
        echo $_GET['term'];
    }
}
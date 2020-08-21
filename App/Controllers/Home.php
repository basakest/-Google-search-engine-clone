<?php

namespace App\Controllers;

use Core\View;
use App\Crawl;

class Home extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Home/index.html');
    }

    public function searchAction()
    {
        if (isset($_GET['term'])) {
            $term = $_GET['term'];
        } else {
            exit('No term is entered');
        }
        $type = isset($_GET['type'])?$_GET['type']:'sites';
        View::renderTemplate('Home/search.html', ['term' => $term, 'type' => $type]);
    }

    public function testAction()
    {
        $crawl = new Crawl();
        $crawl->followLinks($crawl->startUrl);
    }
}
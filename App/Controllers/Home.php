<?php

namespace App\Controllers;

use Core\View;
use App\Crawl;
use App\Models\Site;
use App\Paginator;
use App\Config;

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
        $total = Site::getResultsNum($term);
        
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        } else {
            $page = 1;
        }
        $paginator = new Paginator($page);
        $results = Site::getResults($paginator->offset, $paginator->pageSize, $term);
        /*
        //不想修改sql语句的话这样也可以吧
        foreach($results as $result) {
            $result['title'] = $this->trimField($result['title'], 55);
            $result['description'] = $this->trimField($result['description'], 230);
        }
        */
        /*
        找到0条数据时,$numPages为0，所以$pagesLeft也为0，$page不明确指定时为1
        $startPage = 1 - (10 / 2) = -4，小于1，被赋值1
        在html中
        没有数据被找到时，从Controller中传递来的用于分页的数据为：
        startPage = 1   
        pagesLeft = 0   
        numPages  = 0    
        所以，上面的for语句会变为:
        {% for i in 1..1+0-1 %}即{% for i in 1..0 %}
        0符合下面的if语句，所以分也栏会显示超连接数字0
        解决办法：
        如果$numPages的值为0，则将其赋值为1
        startPage = 1   
        pagesLeft = 1   
        numPages  = 1   
        */
        //页数很多的时候最多只显示10页
        $pagesToShow = 10;
        //该term总共查到多少页数据，向上取整 452 / 20 = 23
        $numPages = ceil($total / Config::PAGE_SIZE)>0?ceil($total / Config::PAGE_SIZE):1;
        //还有多少页数据，超过10取10
        $pagesLeft = min($pagesToShow, $numPages);
        //$currentPage = $page - floor($pagesLeft / 2);
        $startPage = $page - floor($pagesToShow / 2);
        if ($startPage < 1) {
            $startPage = 1;
        }
        //目的是$pagesLeft = 10的时候起作用
        //假设有26页数据，当你访问第26页数据时，
        //$startPage = 26 - (10 / 2) = 21
        //而21-26只显示6页数据
        if ($startPage + $pagesLeft > $numPages + 1) { //18 10 26 || 1 + 0 = 0 + 1
            $startPage = $numPages + 1 - $pagesLeft;
        }
        
        View::renderTemplate('Home/search.html', ['term' => $term, 'type' => $type, 'total' => $total, 'results' => $results, 'page' => $page, 'startPage' => $startPage, 'pagesLeft' => $pagesLeft, 'numPages' => $numPages]);
    }

    public function testAction()
    {
        $crawl = new Crawl();
        $crawl->followLinks($crawl->startUrl);
    }

    private function trimField($string, $characterLimit) {
        $dots = strlen($string) > $characterLimit ? '...' : '';
        return substr($string, 0, $characterLimit) . $dots;
    }

    public function updateLinkClicksAction()
    {
        if (isset($_POST['linkId'])) {
            Site::updateLinkClicks($_POST['linkId']);
        } else {
            echo 'No link passed to page';
        }
        
    }
}
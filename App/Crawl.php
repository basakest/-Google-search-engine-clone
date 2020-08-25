<?php

namespace App;

use App\DomDocumentParser;
use App\Models\Crawl as Crawl_db;
use app\Config;

class Crawl
{
    /**
     * 要爬取的网址
     *
     * @var string
     */
    public $startUrl = "https://www.apple.com/";

    /**
     * 记录已经被爬取的网址的数组
     *
     * @var array
     */
    private $alreadyCrawled = array();

    /**
     * 记录要爬取的网址的数组
     *
     * @var array
     */
    private $crawling = array();

    private $alreadyFoundImages = array();

    /**
     * crawle the specified url, get all the href attributes of a tag
     *
     * @param [string] $url 
     * @return void
     */
    public function followLinks($url)
    {
        $parser = new DomDocumentParser($url);
        //这行代码第一次时获取用$startUrl创建的文档对象中的所有<a>标签
        $linkList = $parser->getLinks();
        
        //这里第一次遍历用$startUrl创建的文档对象中的所有URL
        foreach ($linkList as $link) {
            $href = $link->getAttribute('href');
            if ($href == '') {
                continue;
            }

            //这里应该是有#就不显示了吧，改成strpos($href, '#') == 0会不会更好呢？
            if (strpos($href, '#') !== false) {
                continue;
            }
            if (substr($href, 0, 11) == 'javascript:') {
                continue;
            }
            if (substr($href, 0, 7) == 'mailto:') {
                continue;
            }

            //将网址的相对路径转换为绝对路径
            $href = $this->createLinks($href, $url);
            
            //检查网址有没有被爬取
            /*
            如果要爬取的网址不在已经爬取的网址数组中，则同时将其添加到已爬取和要爬取的数组中,
            然后将该网址的信息记录在sites表中，同时将该网址的所有图片信息记录到image表中
             */
            if (!in_array($href, $this->alreadyCrawled)) {
                $this->alreadyCrawled[] = $href;
                $this->crawling[] = $href;
                $this->getDetails($href);
                array_shift($this->crawling);
            }
        
            //下面这行代码在当前网址已被爬取（记录到数据库）时触发，结束该函数的执行。
            //所以说只要遇到一个已爬取过的网址，就停止递归中一个函数的执行
            //添加这行代码可以显著减少要爬取网站的数量
            //else return;
        } //endof foreach 
        //这个foreach结束之后，$this->alreadyCrawled和$this->crawing中的值就添加了这个网页中a标签的href属性
        

        foreach($this->crawling as $site) {
            $this->followLinks($site);
        }
    }

    public function getDetails($url)
    {
        $parser = new DomDocumentParser($url);
        $titleArray = $parser->getTitleTags();
        //if (sizeof($titleArray) == 0 || $titleArray->item(0)->nodeValue == Null) {
        if (sizeof($titleArray) == 0 || $titleArray->item(0) == NULL) {
            return;
        }

        $title = $titleArray->item(0)->nodeValue;
        $title = str_replace('\n', '', $title);
        if ($title == '') {
            return;
        }
        
        $description = '';
        $keywords = '';
        $metaArray = $parser->getMetaTags();
        foreach($metaArray as $meta) {
            /*
            部分网站的meta标签中为name = 'Description'，
            这种情况下，用name = 'description'作为判断条件不能获取到对应的内容，
            先用strtolower将其转换为小写，再做判断
            */
            if (strtolower($meta->getAttribute('name')) == 'description') {
                $description = $meta->getAttribute('content');
            }
            if (strtolower($meta->getAttribute('name')) == 'keywords') {
                $keywords = $meta->getAttribute('content');
            }
        }
        $description = str_replace('\n', '', $description);
        $keywords = str_replace('\n', '', $keywords);

        if (!Crawl_db::linkExists($url)) {
            Crawl_db::insertLink($url, $title, $description, $keywords);
        }

        //同时爬取该网址中的所有图片
        //获取当前网址中包含所有图片的数组
        $imageArray = $parser->getImages();
        //遍历该数组
        foreach ($imageArray as $image) {
            //获取src, alt, title属性
            $src = $image->getAttribute('src');
            $alt = $image->getAttribute('alt');
            $title_img = $image->getAttribute('title');
            //如果图片没有title和alt属性，则跳过本次处理
            if (!$title_img && $alt) {
                continue;
            }
            //将相对路径转换为绝对路径
            $src = $this->createLinks($src, $url);
            if (!in_array($src, $this->alreadyFoundImages)) {
                $this->alreadyFoundImages[] = $src;
                //爬取图片时没有对已爬取的网站进行记录，因为网址中图片的更新频率可能很频繁
                if (Config::CHECK_IMAGE) {
                    if (!Crawl_db::imageExists($src)) {
                        Crawl_db::insertImage($url, $src, $alt, $title_img);
                    }
                } else {
                    Crawl_db::insertImage($url, $src, $alt, $title_img);
                }
            }
        }
    }

    /**
     * 将不能直接访问的相对路径转换为绝对路径，共有5种情况
     *
     * @param [string] $src 网址中出现的路径
     * @param [string] $url 正在被处理的网址
     * @return $src 
     */
    public function createLinks($src, $url)
    {
        $scheme = parse_url($url)['scheme'];
        $host = parse_url($url)['host'];
        if (substr($src, 0, 2) == '//') {
            $src = $scheme . ':' . $src;
        } elseif (substr($src, 0, 1) == '/') {
            $src = $scheme . '://' . $host . $src;
        } elseif (substr($src, 0, 2) == './') {
            $src = $scheme . '://' . $host . dirname(parse_url($url)['path']). substr($src, 1);
        } elseif (substr($src, 0, 3) == '../') {
            $src = $scheme . '://' . $host . dirname(dirname(parse_url($url)['path'])) . substr($src, 2);
        } elseif (substr($src, 0, 5) != 'https' && substr($src, 0, 4) != 'http') {
            $src = $scheme . '://' . $host . parse_url($url)['path'] . '/' . $src;
        }
        return $src;
    }
}

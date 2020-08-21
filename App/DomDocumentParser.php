<?php

namespace App;

use DOMDocument;

class DomDocumentParser
{
    private $doc;

    /**
     * 根据$url创建相应的DOM文档对象，再将该文档对象的HTML转换为字符串
     *
     * @param [type] $url
     */
    public function __construct($url)
    {
        $options = array('http' => array('method' => 'GET', 'header' => 'User-Agent: doodleBot/0.1\n'));
        $context = stream_context_create($options);
        $this->doc = new DOMDocument();
        @$this->doc->loadHTML(file_get_contents($url, false, $context));
    }

    /**
     * 获取__construct方法创建的文档对象中的所有<a>标签
     *
     * @return DOMNodeList
     */
    public function getLinks()
    {
        return $this->doc->getElementsByTagName('a');
    }

    public function getTitleTags()
    {
        return $this->doc->getElementsByTagName('title');
    }

    public function getMetaTags()
    {
        return $this->doc->getElementsByTagName('meta');
    }

    public function getImages()
    {
        return $this->doc->getElementsByTagName('img');
    }
}

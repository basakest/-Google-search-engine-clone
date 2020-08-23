<?php

namespace App;

use App\Config;

class Paginator
{
    public $page;

    public $pageSize;

    public $offset;

    
    
    public function __construct($page, $pageSize = null)
    {
        $this->page = $page ? $page : 1;
        $this->pageSize = $pageSize ? $pageSize : Config::PAGE_SIZE;
        $this->offset = ($this->page - 1) * $this->pageSize;
    }
}
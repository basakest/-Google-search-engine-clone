<?php

namespace App\Models;

use PDO;

class Crawl extends \Core\Model
{
    public static function insertLink($url, $title, $description, $keywords)
    {
        $db = self::getDB();
        $stmt = $db->prepare("insert into sites(url, title, description, keywords) values (:url, :title, :description, :keywords)");
        $stmt->bindValue(':url', $url, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':keywords', $keywords, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public static function insertImage($url, $src, $alt, $title)
    {
        $db = self::getDB();
        $stmt = $db->prepare("insert into images(siteUrl, imageUrl, alt, title) values (:siteUrl, :imageUrl, :alt, :title)");
        $stmt->bindValue(':siteUrl', $url, PDO::PARAM_STR);
        $stmt->bindValue(':imageUrl', $src, PDO::PARAM_STR);
        $stmt->bindValue(':alt', $alt, PDO::PARAM_STR);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->execute();
        //插入图片失败影响也不大，所以没有return
    }

    /**
     * 如果sites数据库中存在url列等于$url的行，则返回true
     *
     * @param [string] $url
     * @return boolean 数据库中是否存在url列等于$url的行
     */
    public static function linkExists($url)
    {
        $db = static::getDB();
        $stmt = $db->prepare("select count(*) as num from sites where url = :url");
        $stmt->bindValue(':url', $url, PDO::PARAM_STR);
        $stmt->execute();
        $num = $stmt->fetch(PDO::FETCH_ASSOC)['num'];
        //$num的类型为字符串，如果用!==进行比较的话，要用''将0包裹住
        return $num !== '0';
    }

    public static function imageExists($src)
    {
        $db = static::getDB();
        $stmt = $db->prepare("select count(*) as num from images where imageUrl = :imageUrl");
        $stmt->bindValue(':imageUrl', $src, PDO::PARAM_STR);
        $stmt->execute();
        $num = $stmt->fetch(PDO::FETCH_ASSOC)['num'];
        //$num的类型为字符串，如果用!==进行比较的话，要用''将0包裹住
        return $num !== '0';
    }
}
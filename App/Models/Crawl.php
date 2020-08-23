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

    public static function linkExists($url)
    {
        $db = static::getDB();
        $stmt = $db->prepare("select count(*) as num from sites where url = :url");
        $stmt->bindValue(':url', $url, PDO::PARAM_STR);
        $stmt->execute();
        $num = $stmt->fetch(PDO::FETCH_ASSOC)['num'];
        return $num !== '0';
    }
}
<?php

namespace App\Models;

use PDO;

class Image extends \Core\Model
{
    /**
     * 查询包含$term的图片的数量
     *
     * @param [string] $term
     * @return [int] 包含$term的网址的数量
     */
    public static function getResultsNum($term)
    {
        $db = static::getDB();
        $stmt = $db->prepare("select count(*) as total from images where (title like :term or imageUrl like :term or alt like :term) and broken = 0 ");
        $searchTerm = '%' . $term . '%';
        $stmt->bindValue(':term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public static function getResults($offset, $pageSize, $term)
    {
        $db = static::getDB();
        $stmt = $db->prepare("select id, siteUrl, imageUrl, alt, title, clicks,
                              case
                                when title <> '' then title
                                when alt <> '' then alt
                                else imageUrl
                              end as displayText
                              from images
                              where (imageUrl like :term 
                                or alt like :term 
                                or title like :term)
                                and broken = 0
                              order by clicks desc, id
                              limit :pageSize
                              offset :offset");
        $searchTerm = '%' . $term . '%';
        $stmt->bindValue(':term', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        return $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 根据$id的值更新sites表对应的数据行的clicks的数据
     *
     * @param [int] $id
     * @return void
     */
    public static function updateLinkClicks($id)
    {
      $db = static::getDB();
      $stmt = $db->prepare("update sites set clicks = clicks + 1 where id = :id");
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
    }

    public static function removeBrokenImages($src)
    {
      $db = static::getDB();
      $stmt = $db->prepare("update images set broken = 1 where imageUrl = :src");
      $stmt->bindValue(':src', $src, PDO::PARAM_STR);
      $stmt->execute();
    }

    public static function updateImageClicks($imageUrl)
    {
      $db = static::getDB();
      $stmt = $db->prepare("update images set clicks = clicks + 1 where imageUrl = :imageUrl");
      $stmt->bindValue(':imageUrl', $imageUrl, PDO::PARAM_STR);
      $stmt->execute();
    } 
}
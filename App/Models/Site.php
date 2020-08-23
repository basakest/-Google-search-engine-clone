<?php

namespace App\Models;

use PDO;

class Site extends \Core\Model
{
    /**
     * 查询包含$term的网址的数量
     *
     * @param [string] $term
     * @return [int] 包含$term的网址的数量
     */
    public static function getResultsNum($term)
    {
        $db = static::getDB();
        $stmt = $db->prepare("select count(*) as total from sites where title like :term or url like :term or keywords like :term or description like :term");
        $searchTerm = '%' . $term . '%';
        $stmt->bindValue(':term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public static function getResults($offset, $pageSize, $term)
    {
        $db = static::getDB();
        $stmt = $db->prepare("select url, 
                              case when CHARACTER_LENGTH(title) > 55
                                then CONCAT(left(title, 55), '...')
                                else title
                              end as title,
                              case when CHARACTER_LENGTH(description) > 230
                                then CONCAT(left(description, 230), '...')
                                else description
                              end as description,
                              keywords, clicks
                              from sites
                              where title like :term 
                                or url like :term 
                                or keywords like :term 
                                or description like :term 
                              order by clicks desc
                              limit :pageSize
                              offset :offset");
        $searchTerm = '%' . $term . '%';
        $stmt->bindValue(':term', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        return $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
<?php

namespace App\Models;

use PDO;
use PDOException;

class Post extends \Core\Model
{
    public static function getAll()
    {
        
        try {
            $db = static::getDB();
            $stmt = $db->query("select * from posts order by title desc");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
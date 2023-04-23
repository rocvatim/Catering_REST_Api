<?php

namespace App\API;
use PDO;
use App\Plugins\Di\Injectable;

class Tag extends Injectable
{
    public $id;
    public $name;

    public function __construct($id = null, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    // Find a tag by name
    public function find() : int
    {
        $TagIDSql = "SELECT id FROM tag WHERE name = :name";
        $TagIDSql = $this->db->connection->prepare($TagIDSql);
        $TagIDSql->bindParam(":name", $this->name);
        $TagIDSql->execute();
        return $TagIDSql->fetchColumn();
    }

    // Return all tags associated with a id
    public function readAll() : array
    {
        $sql = "SELECT t.id AS tag_id, t.name AS tag_name
        FROM facility_tags ft
        JOIN facility f ON ft.facility_id = f.id
        JOIN tag t ON ft.tag_id = t.id
        WHERE f.id = :id";

        $result = $this->db->connection->prepare($sql);
        $result->bindParam(":id", $this->id, PDO::PARAM_INT);
        $result->execute();

        $tags = [];

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $tags += [$row['tag_id'] => $row['tag_name']];
        }

        return $tags;

    }

    // Create a new tag
    public function create() : int
    {
        $TagSql = "INSERT INTO tag (name) VALUES (:name)";
        $bind = [':name' => $this->name];
        $this->db->executeQuery($TagSql, $bind);

        $id = $this->db->connection->lastInsertId();

        return $id;
    }
}
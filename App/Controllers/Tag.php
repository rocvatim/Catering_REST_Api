<?php

namespace App\Controllers;

class Tag extends BaseController
{

    // Find a tag by name
    public function find($name) : int
    {
        $TagIDSql = "SELECT id FROM tag WHERE name = :name";
        $TagIDSql = $this->db->connection->prepare($TagIDSql);
        $TagIDSql->bindParam(":name", $name);
        $TagIDSql->execute();
        return $TagIDSql->fetchColumn();
    }

    // Create a new tag
    public function create($name) : int
    {
        $TagSql = "INSERT INTO tag (name) VALUES (:name)";
        $bind = [':name' => $name];
        $this->db->executeQuery($TagSql, $bind);

        $id = $this->db->connection->lastInsertId();

        return $id;
    }
}
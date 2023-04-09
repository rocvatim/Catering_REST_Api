<?php

namespace App\Controllers;

use App\Controllers\FacilityController;

class Tag extends FacilityController
{

    // the find function tries to find a tag with the passed through name in the database and returns the id otherwise it returns false
    public function find($name)
    {
        $TagIDSql = "SELECT id FROM tag WHERE name = :name";
        $TagIDSql = $this->db->connection->prepare($TagIDSql);
        $TagIDSql->bindParam(":name", $name);
        $TagIDSql->execute();
        return $TagIDSql->fetchColumn();
    }

    public function create($name)
    {
        $TagSql = "INSERT INTO tag (name) VALUES (:name)";
        $bind = [':name' => $name];
        $this->db->executeQuery($TagSql, $bind);

        $id = $this->db->connection->lastInsertId();

        return $id;
    }
}
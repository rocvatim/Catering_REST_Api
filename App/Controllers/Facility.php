<?php

namespace App\Controllers;

use PDO;
use App\Controllers\FacilityController;

class Facility extends FacilityController
{

    public function readOne($id)
    {
        $sql = "SELECT f.name, f.created_at, l.city, l.address, l.zip_code, l.country_code, l.phone_number,
        (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM facility_tags ft 
         INNER JOIN tag t ON ft.tag_id = t.id WHERE ft.facility_id = f.id) AS tags
         FROM facility f INNER JOIN location l ON f.location_id = l.id
         LEFT JOIN facility_tags ft ON f.id = ft.facility_id
         WHERE f.id = $id;";

        $data = [];

        $result = $this->db->connection->prepare($sql);
        
        $result->execute();

        $data = $result->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function readAll()
    {
        $sql = "SELECT f.name, f.created_at, l.city, l.address, l.zip_code, l.country_code, l.phone_number,
        (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM facility_tags ft 
         INNER JOIN tag t ON ft.tag_id = t.id WHERE ft.facility_id = f.id) AS tags
         FROM facility f
         INNER JOIN location l ON f.location_id = l.id;";
        
        $data = [];

        $result = $this->db->connection->prepare($sql);

        $result->execute();

        // returns rows in a associate array while there are rows to return
        while ($row = $result->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return $data;    
    }

    public function update($name, $location_id, $id)
    {
        $sql = "UPDATE facility SET name = :name, location_id = :location WHERE id = :id";
        $bind = [":name" => $name, ":location" => $location_id, ":id" => $id];
        $this->db->executeQuery($sql, $bind);
    }

    public function create($name, $location_id)
    {
        $sql = "INSERT INTO facility (name, created_at, location_id)
        VALUES (:name, NOW(), :location)";

        $bind = [":name" => $name,":location" => $location_id];

        $this->db->executeQuery($sql, $bind);

        return $this->db->connection->lastInsertId();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM facility WHERE id = :id";
        $bind = [':id' => $id];
        $this->db->executeQuery($sql, $bind);
    }
}
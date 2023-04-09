<?php

namespace App\Controllers;

use PDO;

class Facility extends BaseController
{

    // Return a facility that matches the id
    public function readOne($id) : array
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

    // Return all facilities
    public function readAll() : array
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

    // Update a facility with data that was passed through
    public function update($name, $location_id, $id) : void
    {
        $sql = "UPDATE facility SET name = :name, location_id = :location WHERE id = :id";
        $bind = [":name" => $name, ":location" => $location_id, ":id" => $id];
        $this->db->executeQuery($sql, $bind);
    }

    // Create a new facility with data that was passed through
    public function create($name, $location_id)
    {
        $sql = "INSERT INTO facility (name, created_at, location_id)
        VALUES (:name, NOW(), :location)";

        $bind = [":name" => $name,":location" => $location_id];

        $this->db->executeQuery($sql, $bind);

        return $this->db->connection->lastInsertId();
    }

    // Find all facilities based on a search query
    public function find($query) : array
    {
        $sql = "SELECT f.id, f.name AS facility_name, f.created_at, l.city, l.address, l.zip_code, l.country_code, l.phone_number, GROUP_CONCAT(t.name SEPARATOR ', ') AS tags
        FROM facility f
        INNER JOIN location l ON f.location_id = l.id
        LEFT JOIN facility_tags ft ON f.id = ft.facility_id
        LEFT JOIN tag t ON ft.tag_id = t.id
        WHERE f.name LIKE :searchQuery OR t.name LIKE :searchQuery OR l.city LIKE :searchQuery
        GROUP BY f.id";

        $stmt = $this->db->connection->prepare($sql);
        $stmt->bindValue(':searchQuery', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->execute();

        //Fetch all facilities that match the search query and return them in a associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete the facility by id
    public function delete($id) : void
    {
        $sql = "DELETE FROM facility WHERE id = :id";
        $bind = [':id' => $id];
        $this->db->executeQuery($sql, $bind);
    }
}
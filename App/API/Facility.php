<?php

namespace App\API;

use App\Plugins\Di\Injectable;
use PDO;

class Facility extends Injectable
{
    public $id;
    public $name;
    public $location_id;

    public function __construct($id = null, $name = null, $location_id = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->location_id = $location_id;
    }

    // Return a facility that matches the id
    public function findFacility() : int
    {
        $sql = "SELECT id FROM facility WHERE id = :id";
        $sql = $this->db->connection->prepare($sql);
        $sql->bindParam(":id", $this->id);
        $sql->execute();
        return $sql->fetchColumn();
    }

    // Return a location that matches the id
    public function findLocation() : int
    {
        $sql = "SELECT id FROM location WHERE id = :id";
        $sql = $this->db->connection->prepare($sql);
        $sql->bindParam(":id", $this->location_id);
        $sql->execute();
        return $sql->fetchColumn();
    }

    // Finds a facility and its location and tags based on id
    public function readOne() : array
    {
        $sql = "SELECT f.id, f.name, f.created_at, l.city, l.address, l.zip_code, l.country_code, l.phone_number,
        GROUP_CONCAT(t.name) AS tags
        FROM facility f
        INNER JOIN location l ON f.location_id = l.id
        LEFT JOIN facility_tags ft ON f.id = ft.facility_id
        LEFT JOIN tag t ON ft.tag_id = t.id
        WHERE f.id = :id";

        $data = [];

        $result = $this->db->connection->prepare($sql);
        $result->bindParam(":id", $this->id, PDO::PARAM_INT);
        $result->execute();

        $row = $result->fetch(PDO::FETCH_ASSOC);
        $tags = new Tag($this->id);

        // returns rows in a associate array while there are rows to return
        if ($row){
            $facility = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'location' => array(
                    'city' => $row['city'],
                    'address' => $row['address'],
                    'zip_code' => $row['zip_code'],
                    'country_code' => $row['country_code'],
                    'phone_number' => $row['phone_number']
                ),
                'tags' => $tags->readAll()
            );
            return $facility;
        } else {
            return ["Error" => "Facility not found"];
        }

         
    }

    // Return all facilities
    public function readAll() : array
    {
        $sql = "SELECT f.id, f.name, f.created_at, l.city, l.address, l.zip_code, l.country_code, l.phone_number,
        GROUP_CONCAT(t.name) AS tags
        FROM facility f
        INNER JOIN location l ON f.location_id = l.id
        LEFT JOIN facility_tags ft ON f.id = ft.facility_id
        LEFT JOIN tag t ON ft.tag_id = t.id
        GROUP BY f.id";
        
        $data = [];

        $result = $this->db->connection->prepare($sql);
        $result->execute();

        // returns rows in a associate array while there are rows to return
        $facilities = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $tags = new Tag($row['id']);
            $facility = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'created_at' => $row['created_at'],
                'location' => array(
                    'city' => $row['city'],
                    'address' => $row['address'],
                    'zip_code' => $row['zip_code'],
                    'country_code' => $row['country_code'],
                    'phone_number' => $row['phone_number']
                ),
                'tags' => $tags->readAll()
            );
            $facilities[] = $facility;
        }

        return $facilities;    
    }

    // Update a facility with data that was passed through
    public function update() : void
    {
        $sql = "UPDATE facility SET name = :name, location_id = :location WHERE id = :id";
        $bind = [":name" => $this->name, ":location" => $this->location_id, ":id" => $this->id];
        $this->db->executeQuery($sql, $bind);
    }

    // Create a new facility with data that was passed through
    public function create()
    {
        $sql = "INSERT INTO facility (name, created_at, location_id)
        VALUES (:name, NOW(), :location)";

        $bind = [":name" => $this->name,":location" => $this->location_id];

        $this->db->executeQuery($sql, $bind);

        return $this->db->connection->lastInsertId();
    }

    // Find all facilities based on a search query
    public function search($name,$city,$tag) : array
    {
        $sql = "SELECT f.id, f.name AS facility_name, f.created_at, l.city, l.address, l.zip_code, l.country_code, l.phone_number, GROUP_CONCAT(t.name) AS tags
        FROM facility f
        INNER JOIN location l ON f.location_id = l.id
        INNER JOIN facility_tags ft ON f.id = ft.facility_id
        INNER JOIN tag t ON ft.tag_id = t.id";

        $whereClauses = [];
        $params = array();

        if ($name) {
            $whereClauses[] .= "f.name LIKE :name";
            $params[":name"] = '%' . $name . '%';
        }

        if ($tag) {
            $whereClauses[] .= "t.name LIKE :tag";
            $params[":tag"] =  '%' . $tag . '%';
        }

        if ($city) {
            $whereClauses[] .= "l.city LIKE :city";
            $params[":city"] = '%' . $city . '%';
        }


        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
            
        }

        $sql .= " GROUP BY f.id";

        $stmt = $this->db->connection->prepare($sql);
        $stmt->execute($params);

        //Fetch all facilities that match the search query and return them in a associative array
        $facilities = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $facility = array(
                'id' => $row['id'],
                'name' => $row['facility_name'],
                'created_at' => $row['created_at'],
                'location' => array(
                    'city' => $row['city'],
                    'address' => $row['address'],
                    'zip_code' => $row['zip_code'],
                    'country_code' => $row['country_code'],
                    'phone_number' => $row['phone_number']
                ),
                'tags' => array()
            );

            // Seperate the tags
            if ($row['tags']) {
                $tags = explode(',', $row['tags']);
                foreach ($tags as $index => $tag) {
                    $facility['tags'][$index + 1] = $tag;
                }
            }

            $facilities[] = $facility;
        }

        return $facilities;
        
    }

    // Delete the facility by id
    public function delete() : void
    {
        $sql = "DELETE FROM facility WHERE id = :id";
        $bind = [':id' => $this->id];
        $this->db->executeQuery($sql, $bind);
    }
}
<?php

namespace App\Controllers;

use PDO;

class FacilityController extends BaseController
{

    public function createFacility()
    {
        $data = [];

        $conn  = $this->db->connection;

        parse_str(file_get_contents("php://input"), $data);

        //Seperate tags string into a array
        $data['tags'] = explode(',', $data['tags']);

        //Check for errors
        $errors = $this->getValidationErrors($data);

        if ( ! empty($errors)) {
            http_response_code(422);
            echo json_encode(["errors" => $errors]);
        }

        //Begin transaction to ensure that all statements are executed or none are
        $conn->beginTransaction();

        //Prepare statement to insert a new facility
        $sql = $conn->prepare("INSERT INTO facility (name, created_at, location_id)
        VALUES (:name, NOW(), :location)");

        $sql->bindParam(":name", $data['name']);
        $sql->bindParam(":location", $data['location_id']);

        //insert a new facility
        $sql->execute();

        //Get the facility id assigned to the new facility
        $facilityID = $conn->lastInsertId();

        //Prepare statement to insert new tags
        $stmtTag = $conn->prepare("INSERT INTO tag (name) VALUES (:name)");

        //Prepare statement to retrieve id for an existing tag
        $stmtTagID = $conn->prepare("SELECT id FROM tag WHERE name = :name");

        //Prepare statement to insert a new facility_tag record
        $stmtFacilityTag = $conn->prepare("INSERT INTO facility_tags (facility_id, tag_id) values (:facility, :tag)");

        // Loop through the tags associated with the new facility
        foreach ($data['tags'] as $tagName) {
            // Check if the tag already exists in the tag table
            $stmtTagID->bindParam(":name", $tagName);
            $stmtTagID->execute();
            $tagID = $stmtTagID->fetchColumn();

            // If the tag does not exist, insert a new record into the tag table
            if (!$tagID) {
                $stmtTag->bindParam(":name", $tagName);
                $stmtTag->execute();
                $tagID = $this->conn->lastInsertId();
            }

            // Insert a new record into facility_tags that links the new facility with the tag
            $stmtFacilityTag->bindParam(":facility", $facilityID);
            $stmtFacilityTag->bindParam(":tag", $tagID);
            $stmtFacilityTag->execute(); 

        }
        
        // Commit the transaction
        $conn->commit();

        // Return the Id of the new facility
        http_response_code(201);
        echo json_encode([
            "message" => "Facility created",
            "id" => $facilityID
        ]);
        
        // Stop further execution
        return;
    }

    public function facilityRequest(string $id): void
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

        echo json_encode($data);

    }

    public function allFacilitiesRequest(): void
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

        echo json_encode($data);
    }

    public function updateFacility($id) : void
    {      
        $data = [];
        parse_str(file_get_contents("php://input"), $data);

        $conn = $this->db->connection;

        // Prepare the SQL statement to update the facility
        $sql = "UPDATE facility SET name = ?, location_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$data['name'],$data['location_id'],$id]);

        // Delete existing tags for the facility
        $sql = 'DELETE FROM facility_tags WHERE facility_id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        // Insert new tags for the facility
        $tagsArr = explode(',', $data['tags']);
        foreach ($tagsArr as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                // Check if the tag already exists
                $sql = "SELECT id FROM tag WHERE name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$tag]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    // Use the existing tag ID
                    $tagId = $result['id'];
                } else {
                    // Insert a new tag and get its id
                    $sql = 'INSERT INTO tag (name) VALUES (?)';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$tag]);
                    $tagId = $conn->lastInsertId();
                }

                // Associate the tag with the facility
                $sql = 'INSERT INTO facility_tags (facility_id, tag_id) VALUES (?, ?)';
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id, $tagId]);
            }
        }

        echo json_encode("Record Updated Succesfully");
    }

    public function deleteFacility($id) : void
    {
        $conn = $this->db->connection;

        // Delete tags associated with the facility
        $sql = "DELETE FROM facility_tags WHERE facility_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        // Delete the facility itself
        $sql = "DELETE FROM facility WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);

        echo json_encode("Record deleted succesfully");
    }

    public function searchFacilities() : void
    {
        // Retrieves the search query
        $searchQuery = $_GET['q'];

        $conn = $this->db->connection;

        $sql = "SELECT f.id, f.name AS facility_name, f.created_at, l.city, l.address, l.zip_code, l.country_code, l.phone_number, GROUP_CONCAT(t.name SEPARATOR ', ') AS tags
        FROM facility f
        INNER JOIN location l ON f.location_id = l.id
        LEFT JOIN facility_tags ft ON f.id = ft.facility_id
        LEFT JOIN tag t ON ft.tag_id = t.id
        WHERE f.name LIKE :searchQuery OR t.name LIKE :searchQuery OR l.city LIKE :searchQuery
        GROUP BY f.id";

        //Prepares the query to search for a facility
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    }

    //Checks if all required data is passed through
    private function getValidationErrors(array $data): array
    {
        $errors = [];

        if (empty($data["name"])) {
            $errors[] = "name is required";
        }

        if (empty($data["location_id"])) {
            $errors[] = "location_id is required";
        }

        if (filter_var($data["location_id"], FILTER_VALIDATE_INT) === false) {
            $errors[] = "location_id must be a integer";
        }

        return $errors;
    }

}
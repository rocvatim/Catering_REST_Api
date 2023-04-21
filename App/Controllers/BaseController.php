<?php
namespace App\Controllers;

use App\Plugins\Di\Injectable;

use App\API\Facility;
use App\API\FacilityTags;
use App\API\Tag;

class BaseController extends Injectable {

    
    public function createFacility() : void
    {

        //Collect the data for the facility that will be created
        $data = [];
        parse_str(file_get_contents("php://input"), $data);

        

        //Check for errors
        $errors = $this->getValidationErrors($data);

        // If any errors are given return a Unprocessable Content response code and the errors
        if ( ! empty($errors)) {
            http_response_code(422);
            echo json_encode(["errors" => $errors]);
        }

        //Begin transaction to ensure that all statements are executed or none are
        $this->db->beginTransaction();

        // Check if the given location_id exists
        $facility = new Facility($id = null,$data['name'],$data['location_id']);
        $locationID = $facility->findLocation();

        //Insert a new facility
        if ($locationID){
            $facilityID = $facility->create();

            // Loop through the tags associated with the new facility
            if ($data['tags']){
                //Seperate tags string into a array
                $data['tags'] = explode(',', $data['tags']);

                foreach ($data['tags'] as $tagName) {
                        // Check if the tag already exists in the tag table
                        $tag = new Tag($id = null, $tagName);
                        $tagID = $tag->find();
            
                        // If the tag does not exist, insert a new record into the tag table
                        if (!$tagID) {
                            $tagID = $tag->create();
                        }
            
                        // Insert a new record into facility_tags that links the new facility with the tag
                        $junction = new FacilityTags($facilityID,$tagID);
                        $junction->create();
        
                    }
            }
            // Commit the transaction
            $this->db->commit();

            // Return the Id of the new facility
            http_response_code(201);
            echo json_encode([
                "message" => "Facility created",
                "id" => $facilityID
            ]);
        } else {
            echo json_encode([
                "Error" => "Location Id was not found"
            ]);
        }
        
        
        
        
        
        
    }

    public function facilityRequest(string $id) : void
    {
        // Return the facility associated with id
        $facility = new Facility($id);
        echo json_encode($facility->readOne());

    }

    public function allFacilitiesRequest() : void
    {
        //Return all facilities
        $facility = new Facility;
        echo json_encode($facility->readAll());
    }

    public function updateFacility($id) : void
    {  
        $data = [];
        parse_str(file_get_contents("php://input"), $data);

        // Update the facility
        $facility = new Facility($id,$data['name'],$data['location_id']);
        $facility->update();

        // Delete the junction between tags for the facility
        $junction = new FacilityTags($id);
        $junction->delete();

        // Insert new tags for the facility
        $tagsArr = explode(',', $data['tags']);
        foreach ($tagsArr as $newTag) {
            $newTag = trim($newTag);
            if (!empty($newTag)) {
                // Check if the tag already exists
                $tag = new Tag($tagId = null, $newTag);
                $result = $tag->find();
                if (!$result) {
                    // If no tag was found insert a new tag and get its id
                    $tagId = $tag->create();
                } else {
                    // Use the existing tag ID
                    $tagId = $result;
                }

                // Insert a new record into facility_tags that links the new facility with the tag
                $junction  = new FacilityTags($id,$tagId);
                $junction->create();
            }
        }

        echo json_encode([
            "Message" => "Record Updated Succesfully",
            "Record" => $facility->readOne($id)
        ]);
    }

    public function deleteFacility($id) : void
    {
        $facility = new Facility($id);
        if ($facility->findFacility()){
            // Delete the facility itself
            $facility->delete();
            echo json_encode([
                "message" => "Record " . $id . " deleted succesfully"
            ]);
        } else {
            echo json_encode([
                "Error" => "Record " . $id . " was not found"
            ]);
        }
        

        
        

        
    }

    public function searchFacilities() : void
    {
        // Retrieves the search query
        if (isset($_GET['name'])){
            $nameQuery = $_GET['name'];
        } else {
            $nameQuery = null;
        }
        
        if (isset($_GET['city'])){
            $cityQuery = $_GET['city'];
        } else {
            $cityQuery = null;
        }
        
        if (isset($_GET['tag'])){
            $tagQuery = $_GET['tag'];
        } else {
            $tagQuery = null;
        }

        // Find all facilities that match with the search query
        $facility = new Facility();
        $results = $facility->search($nameQuery,$cityQuery,$tagQuery);
        if ($results) {
            echo json_encode($results);
        } else {
            echo json_encode([
                "Message" => "No matching facilities found"
            ]);
        }
        
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

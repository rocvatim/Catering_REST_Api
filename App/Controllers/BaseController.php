<?php
namespace App\Controllers;

use App\Plugins\Di\Injectable;

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

        //Insert a new facility
        $facility = new Facility;
        $facilityID = $facility->create($data['name'],$data['location_id']);
        
        // Loop through the tags associated with the new facility
        if($data['tags']){
            //Seperate tags string into a array
            $data['tags'] = explode(',', $data['tags']);

            foreach ($data['tags'] as $tagName) {
                // Check if the tag already exists in the tag table
                $tag = new Tag();
                $tagID = $tag->find($tagName);
    
                // If the tag does not exist, insert a new record into the tag table
                if (!$tagID) {
                    $tagID = $tag->create($tagName);
                }
    
                // Insert a new record into facility_tags that links the new facility with the tag
                $junction = new FacilityTags;
                $junction->create($facilityID, $tagID);
    
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
    }

    public function facilityRequest(string $id) : void
    {
        // Return the facility associated with id
        $facility = new Facility;
        echo json_encode($facility->readOne($id));

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
        $junction = new FacilityTags;
        $junction->delete($id);

        // Insert new tags for the facility
        $tagsArr = explode(',', $data['tags']);
        foreach ($tagsArr as $newTag) {
            $newTag = trim($newTag);
            if (!empty($newTag)) {
                // Check if the tag already exists
                $tag = new Tag;
                $result = $tag->find($newTag);
                if (!$result) {
                    // If no tag was found insert a new tag and get its id
                    $tagId = $tag->create($newTag);
                } else {
                    // Use the existing tag ID
                    $tagId = $result;
                }

                // Insert a new record into facility_tags that links the new facility with the tag
                $junction = new FacilityTags;
                $junction->create($id, $tagId);
            }
        }

        echo json_encode([
            "Message" => "Record Updated Succesfully",
            "Record" => $facility->readOne($id)
        ]);
    }

    public function deleteFacility($id) : void
    {
        $facility = new Facility;
        if ($facility->find($id)){
            // Delete the facility itself
            $facility->delete($id);
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
        $searchQuery = $_GET['q'];

        // Find all facilities that match with the search query
        $facility = new Facility;
        echo json_encode($facility->search($searchQuery));
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

<?php

namespace App\API;
use App\Plugins\Di\Injectable;

class FacilityTags extends Injectable
{
    public $facility_id;
    public $tag_id;

    public function __construct($facility_id = null, $tag_id = null)
    {
        $this->facility_id = $facility_id;
        $this->tag_id = $tag_id;
    }
    //Create a new junction between tags and a facility
    public function create() : void
    {
        $sql = "INSERT INTO facility_tags (facility_id, tag_id) values (:facility, :tag)";
        $bind = [":facility" => $this->facility_id, ":tag" => $this->tag_id];
        $this->db->executeQuery($sql, $bind);
    }

    // Delete a junction between tags and a facility
    public function delete() : void
    {
        $sql = "DELETE FROM facility_tags WHERE facility_id = :id";
        $bind = [":id" => $this->facility_id];
        $this->db->executeQuery($sql, $bind);
    }
}
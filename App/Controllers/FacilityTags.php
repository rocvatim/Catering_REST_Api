<?php

namespace App\Controllers;

class FacilityTags extends BaseController
{
    //Create a new junction between tags and a facility
    public function create($facility_id, $tag_id) : void
    {
        $FacilityTagSql = "INSERT INTO facility_tags (facility_id, tag_id) values (:facility, :tag)";
        $bind = [":facility" => $facility_id, ":tag" => $tag_id];
        $this->db->executeQuery($FacilityTagSql, $bind);
    }

    // Delete a junction between tags and a facility
    public function delete($facility_id) : void
    {
        $sql = "DELETE FROM facility_tags WHERE facility_id = :id";
        $bind = [":id" => $facility_id];
        $this->db->executeQuery($sql, $bind);
    }
}
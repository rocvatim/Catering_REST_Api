<?php

namespace App\Controllers;

use App\Controllers\FacilityController;

class Junction extends FacilityController
{

    public function create($facility_id, $tag_id)
    {
        $FacilityTagSql = "INSERT INTO facility_tags (facility_id, tag_id) values (:facility, :tag)";
        $bind = [":facility" => $facility_id, ":tag" => $tag_id];
        $this->db->executeQuery($FacilityTagSql, $bind);
    }

    public function delete($facility_id)
    {
        $sql = "DELETE FROM facility_tags WHERE facility_id = :id";
        $bind = [":id" => $facility_id];
        $this->db->executeQuery($sql, $bind);
    }
}
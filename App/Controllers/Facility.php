<?php

use App\Controllers\FacilityController;

class Facility extends FacilityController
{
    public string $name;
    public int $location_id;

    public function __construct($name, $location_id)
    {
        $this->name = $name;
        $this->location_id = $location_id;
    }

    public function readOne()
    {
        $facility = new FacilityController;
        $facility->facilityRequest(3);
    }
}
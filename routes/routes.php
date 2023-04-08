<?php

/** @var Bramus\Router\Router $router */


// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/facilities', App\Controllers\FacilityController::class . '@allFacilitiesRequest');
$router->get('/facilities/search', App\Controllers\FacilityController::class . '@searchFacilities');
$router->get('/facilities/{id}', App\Controllers\FacilityController::class . '@facilityRequest');
$router->post('/facilities', App\Controllers\FacilityController::class . '@createFacility');
$router->patch('/facilities/{id}', App\Controllers\FacilityController::class . '@updateFacility');
$router->delete('/facilities/{id}', App\Controllers\FacilityController::class . '@deleteFacility');
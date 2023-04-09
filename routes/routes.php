<?php

/** @var Bramus\Router\Router $router */


// Define routes here
$router->get('/test', App\Controllers\IndexController::class . '@test');
$router->get('/facilities', App\Controllers\BaseController::class . '@allFacilitiesRequest');
$router->get('/facilities/search', App\Controllers\BaseController::class . '@searchFacilities');
$router->get('/facilities/{id}', App\Controllers\BaseController::class . '@facilityRequest');
$router->post('/facilities', App\Controllers\BaseController::class . '@createFacility');
$router->patch('/facilities/{id}', App\Controllers\BaseController::class . '@updateFacility');
$router->delete('/facilities/{id}', App\Controllers\BaseController::class . '@deleteFacility');
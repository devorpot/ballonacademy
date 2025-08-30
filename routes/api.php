<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationsController;

Route::get('/locations/countries', [LocationsController::class, 'countries']);
Route::get('/locations/states/{country}', [LocationsController::class, 'states']);

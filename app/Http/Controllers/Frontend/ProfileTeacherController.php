<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileTeacherController extends Controller
{
   

   public function index(){
     return "Esta seccion es reservada para maestros, esta en desarrollo";
   }

  
}

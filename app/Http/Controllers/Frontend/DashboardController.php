<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function index()
    {
        $user = auth()->user();

        $courses = $user->courses()->withCount('videos')->get();

        return \Inertia\Inertia::render('Frontend/Dashboard/Index', [
            'courses' => $courses,
        ]);
    }

}

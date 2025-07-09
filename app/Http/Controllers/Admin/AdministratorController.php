<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AdministratorController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard/Index');
    }
}

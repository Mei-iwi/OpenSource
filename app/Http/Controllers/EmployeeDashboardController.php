<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard.employee');
    }
}

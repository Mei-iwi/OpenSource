<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        $attendances = $employee
            ? $employee->attendances()->when($request->month, fn ($query, $month) => $query->whereMonth('work_date', $month))->when($request->year, fn ($query, $year) => $query->whereYear('work_date', $year))->latest('work_date')->paginate(15)->withQueryString()
            : collect();

        return view('employee.attendances.index', compact('employee', 'attendances'));
    }
}

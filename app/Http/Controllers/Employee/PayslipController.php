<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayslipController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        $payslips = Payslip::with('period')
            ->where('employee_id', $employee?->id ?? 0)
            ->whereHas('period', fn ($query) => $query->where('status', 'finalized'))
            ->latest('id')->paginate(12);

        return view('employee.payslips.index', compact('payslips'));
    }

    public function show(Request $request, Payslip $payslip): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $payslip->employee_id === $employee->id && $payslip->period->status === 'finalized', 404);

        return view('payroll.payslip', ['payslip' => $payslip, 'isHr' => false]);
    }
}

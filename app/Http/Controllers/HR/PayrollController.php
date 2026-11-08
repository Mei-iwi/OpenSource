<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneratePayrollRequest;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Services\PayrollMoney;
use App\Services\PayrollService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(): View
    {
        $periods = PayrollPeriod::withCount('payslips')->orderByDesc('period_month')->paginate(12);

        return view('hr.payroll.index', compact('periods'));
    }

    public function generate(GeneratePayrollRequest $request, PayrollService $payroll): RedirectResponse
    {
        try {
            $period = $payroll->generate($request->validated('month'));
        } catch (DomainException $exception) {
            return back()->withErrors(['month' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('hr.payroll.show', $period)->with('success', 'Đã tạo bảng lương nháp.');
    }

    public function show(PayrollPeriod $period): View
    {
        $period->load(['payslips' => fn ($query) => $query->orderBy('employee_code'), 'finalizer']);
        $totalNetCents = $period->payslips->reduce(fn (int $total, Payslip $payslip) => $total + PayrollMoney::cents($payslip->net_pay), 0);

        return view('hr.payroll.show', ['period' => $period, 'totalNet' => PayrollMoney::decimal($totalNetCents)]);
    }

    public function finalize(Request $request, PayrollPeriod $period, PayrollService $payroll): RedirectResponse
    {
        try {
            $payroll->finalize($period, $request->user());
        } catch (DomainException $exception) {
            return back()->withErrors(['payroll' => $exception->getMessage()]);
        }

        return redirect()->route('hr.payroll.show', $period)->with('success', 'Đã chốt kỳ lương.');
    }

    public function payslip(PayrollPeriod $period, Payslip $payslip): View
    {
        abort_unless($payslip->payroll_period_id === $period->id, 404);
        $payslip->setRelation('period', $period);

        return view('payroll.payslip', ['payslip' => $payslip, 'isHr' => true]);
    }
}

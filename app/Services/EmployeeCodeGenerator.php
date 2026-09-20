<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EmployeeCodeGenerator
{
    public function next(string $role): string
    {
        return DB::transaction(function () use ($role) {
            $prefix = $this->prefix($role);
            $sequence = DB::table('employee_code_sequences')->where('prefix', $prefix)->lockForUpdate()->firstOrFail();
            $code = $this->availableCode($prefix, $sequence->last_number + 1);
            DB::table('employee_code_sequences')->where('prefix', $prefix)->update(['last_number' => (int) substr($code, strlen($prefix) + 1)]);

            return $code;
        });
    }

    private function prefix(string $role): string
    {
        return match ($role) {
            'employee' => 'EMP',
            'hr' => 'HR',
            default => throw new InvalidArgumentException('Vai trò không hỗ trợ tạo mã nhân viên.'),
        };
    }

    private function availableCode(string $prefix, int $number): string
    {
        do {
            $code = $prefix.'-'.str_pad((string) $number++, 4, '0', STR_PAD_LEFT);
        } while (Employee::where('employee_code', $code)->exists());

        return $code;
    }
}

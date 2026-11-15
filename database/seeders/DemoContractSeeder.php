<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class DemoContractSeeder extends Seeder
{
    /**
     * Synthetic contract examples for the accounts defined in DatabaseSeeder.
     * Only entirely blank contracts are populated; entered data is never replaced.
     */
    public function run(): void
    {
        $contracts = [
            'ADM-0001' => ['indefinite', '2021-03-15', null],
            'HR-0001' => ['indefinite', '2021-08-02', null],
            'HR-0002' => ['fixed-term', '2025-01-01', '2027-12-31'],
            'IT-0001' => ['indefinite', '2022-04-11', null],
            'IT-0002' => ['fixed-term', '2026-01-01', '2027-12-31'],
            'IT-0003' => ['fixed-term', '2026-03-18', '2027-03-17'],
            'IT-0004' => ['indefinite', '2023-06-05', null],
            'KD-0001' => ['indefinite', '2020-09-14', null],
            'KD-0002' => ['fixed-term', '2026-01-01', '2027-12-31'],
            'KD-0003' => ['fixed-term', '2025-05-15', '2026-11-30'],
            'KD-0004' => ['fixed-term', '2025-02-03', '2027-02-02'],
            'KD-0005' => ['fixed-term', '2024-07-04', '2025-07-03'],
            'KT-0001' => ['indefinite', '2020-02-10', null],
            'KT-0002' => ['fixed-term', '2025-08-07', '2027-08-06'],
            'KT-0003' => ['fixed-term', '2026-01-01', '2027-12-31'],
            'CS-0001' => ['indefinite', '2022-06-13', null],
            'CS-0002' => ['fixed-term', '2025-11-20', '2027-11-19'],
            'CS-0003' => ['fixed-term', '2025-01-06', '2027-01-05'],
            'HC-0001' => ['indefinite', '2023-03-06', null],
            'HC-0002' => ['fixed-term', '2026-01-01', '2027-12-31'],
        ];

        foreach ($contracts as $code => [$type, $start, $end]) {
            Employee::query()
                ->where('employee_code', $code)
                ->whereHas('user', fn ($query) => $query->where('email', 'like', '%@%.hr-management.com'))
                ->whereNull('contract_type')
                ->whereNull('contract_start_date')
                ->whereNull('contract_end_date')
                ->update([
                    'contract_type' => $type,
                    'contract_start_date' => $start,
                    'contract_end_date' => $end,
                ]);
        }
    }
}

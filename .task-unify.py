from pathlib import Path
import re
def edit(p,fn):
 f=Path(p);f.write_text(fn(f.read_text(encoding='utf-8')),encoding='utf-8')
p='app/Http/Controllers/Admin/UserController.php'
s=Path(p).read_text(encoding='utf-8').replace('use App\\Http\\Requests\\StoreUserRequest;\n','').replace('use Illuminate\\Support\\Facades\\Hash;','use App\\Services\\EmployeeCodeGenerator;')
a=s.index('    /**\n     * Show the form for creating');b=s.index('    /**\n     * Display the specified',a);s=s[:a]+s[b:]
s=s.replace("        return view('admin.users.show', compact('user'));", "        $roleChanges = $user->roleChanges()->with('actor')->latest('id')->paginate(15);\n\n        return view('admin.users.show', compact('user', 'roleChanges'));")
s=s.replace('public function update(UpdateUserRequest $request, User $user)', 'public function update(UpdateUserRequest $request, User $user, EmployeeCodeGenerator $codes)')
s=s.replace('DB::transaction(function () use ($user, $data) {','DB::transaction(function () use ($user, $data, $codes) {\n            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();')
s=s.replace("isset($data['employee_code'])", "isset($data['department_id'])")
s=s.replace("only(['employee_code', 'department_id', 'hire_date'])->all() + ['employment_status' => 'active']", "only(['department_id', 'hire_date'])->all() + ['employee_code' => $codes->next($user->role), 'employment_status' => 'active']")
Path(p).write_text(s,encoding='utf-8')
p='app/Http/Controllers/HR/EmployeeController.php';s=Path(p).read_text(encoding='utf-8')
s=s.replace('use App\\Http\\Requests\\EmployeeCodeAvailabilityRequest;', 'use App\\Services\\EmployeeCodeGenerator;\nuse Illuminate\\Http\\Request;\nuse Illuminate\\Validation\\Rule;')
a=s.index('    public function checkCode(');b=s.index('    /**',a)
s=s[:a]+'''    public function codePreview(Request $request, EmployeeCodeGenerator $codes): JsonResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in($request->user()->isAdmin() ? ['hr', 'employee'] : ['employee'])]]);

        return response()->json(['code' => $codes->preview($data['role'])])->header('Cache-Control', 'no-store');
    }

'''+s[b:]
s=s.replace('public function store(StoreEmployeeRequest $request)', 'public function store(StoreEmployeeRequest $request, EmployeeCodeGenerator $codes)')
s=s.replace('DB::transaction(function () use ($data, $avatarPath) {', '$employee = DB::transaction(function () use ($data, $avatarPath, $codes) {')
s=s.replace("'role' => 'employee'", "'role' => $data['role']")
s=s.replace("['name', 'email', 'password', 'password_confirmation', 'avatar']", "['name', 'email', 'password', 'password_confirmation', 'avatar', 'role', 'employee_code']")
s=s.replace("                $user->employee()->create($employeeData);", "                $employeeData['employee_code'] = $codes->next($user->role);\n\n                return $user->employee()->create($employeeData);")
s=s.replace("'Đã tạo tài khoản và hồ sơ nhân viên.'", "'Đã tạo nhân viên '.$employee->employee_code.' và tài khoản đăng nhập.'")
Path(p).write_text(s,encoding='utf-8')
edit('routes/web.php', lambda s:s.replace("    Route::resource('users', UserController::class);", "    Route::redirect('/users/create', '/hr/employees/create');\n    Route::resource('users', UserController::class)->except(['create', 'store']);").replace("Route::get('/employees/check-code', [EmployeeController::class, 'checkCode'])->name('employees.check-code')", "Route::get('/employees/code-preview', [EmployeeController::class, 'codePreview'])->name('employees.code-preview')"))
edit('resources/views/admin/users/index.blade.php',lambda s:re.sub(r'<a href="\{\{ route\(\'admin.users.create\'\).*?</a>','',s).replace('Tạo và quản lý quyền truy cập của HR và Employee.','Quản lý quyền truy cập. Tạo nhân viên và tài khoản tại mục Nhân viên.'))
edit('resources/views/admin/users/_form.blade.php',lambda s:re.sub(r'@if \(!isset\(\$user\)\).*?@endif','',s).replace("{{ isset($user) ? 'Lưu thay đổi' : 'Tạo tài khoản' }}",'Lưu thay đổi'))
p='resources/views/admin/users/_employee-profile.blade.php';s=Path(p).read_text(encoding='utf-8')
a=s.index('            <div>\n                <label for="employee_code"');b=s.index('            <div>',a+20)
s=s[:a]+"            <x-employee-code-preview />\n"+s[b:];Path(p).write_text(s,encoding='utf-8')
p='resources/views/hr/employees/_form.blade.php';s=Path(p).read_text(encoding='utf-8')
s=re.sub(r'    <div><label for="employee_code".*?</div>', '''    @if (!isset($employee))
        <div><label for="role" class="app-label">Vai trò tài khoản</label>
            <select id="role" name="role" required class="app-input w-full">
                <option value="">Chọn vai trò</option>
                <option value="employee" @selected(old('role') === 'employee')>Nhân viên (EMP)</option>
                @if(auth()->user()->isAdmin())<option value="hr" @selected(old('role') === 'hr')>Nhân sự (HR)</option>@endif
            </select><x-input-error :messages="$errors->get('role')" />
        </div>
        <x-employee-code-preview />
    @else
        <div><span class="app-label">Mã nhân viên</span><p class="mt-1 font-semibold">{{ $employee->employee_code }}</p><p class="app-subtitle">Mã được giữ nguyên, kể cả khi đổi vai trò.</p><x-input-error :messages="$errors->get('employee_code')" /></div>
    @endif''',s)
s=s[:s.index("@push('scripts')")]
s=s.replace("'Tạo nhân viên'", "'Tạo nhân viên và tài khoản'")
Path(p).write_text(s,encoding='utf-8')
edit('resources/views/hr/employees/create.blade.php',lambda s:s.replace('Tạo nhân viên','Tạo nhân viên và tài khoản').replace('Tạo đồng thời tài khoản Employee và hồ sơ nhân sự.','Một lần nhập thông tin để tạo hồ sơ nhân viên và tài khoản đăng nhập. Mã được sinh tự động theo vai trò.'))
edit('resources/views/hr/employees/index.blade.php',lambda s:s.replace('+ Thêm nhân viên','+ Tạo nhân viên và tài khoản'))
edit('database/seeders/DatabaseSeeder.php',lambda s:s.replace("'employee_code' => 'ADM-0001',", "'employee_code' => $admin->employee?->employee_code ?? 'ADM-0001',").replace("'employee_code' => $item['employee_code'],", "'employee_code' => $user->employee?->employee_code ?? $item['employee_code'],").replace("'employee_code' => $data['code'],", "'employee_code' => $user->employee?->employee_code ?? $data['code'],"))
for p in ['app/Http/Requests/StoreUserRequest.php','app/Http/Requests/EmployeeCodeAvailabilityRequest.php','resources/views/admin/users/create.blade.php']:
 Path(p).unlink()

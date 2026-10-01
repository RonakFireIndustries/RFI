<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeService
{
    public function getEmployees(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with(['department', 'designation', 'manager', 'user']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('emp_id', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['designation_id'])) {
            $query->where('designation_id', $filters['designation_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('employment_type', $filters['status']);
        }

        if (!empty($filters['manager_id'])) {
            $query->where('reporting_manager_id', $filters['manager_id']);
        }

        return $query->paginate($perPage);
    }

    private function processUploads(array &$data): void
    {
        $fileFields = [
            'photo' => 'photo_path',
            'resume' => 'resume_path',
            'aadhaar' => 'aadhaar_path',
            'pan' => 'pan_path',
            'offer_letter' => 'offer_letter_path',
        ];

        foreach ($fileFields as $input => $dbColumn) {
            if (isset($data[$input]) && $data[$input] instanceof \Illuminate\Http\UploadedFile) {
                $path = $data[$input]->store('employees', 'public');
                $data[$dbColumn] = $path;
                unset($data[$input]);
            }
        }
    }

    public function createEmployee(array $data): array
    {
        $this->processUploads($data);

        [$employee, $credentials] = DB::transaction(function () use ($data) {
            $employee = Employee::create($data);

            // Phase 5: Employee User Linking (if requested auto creation)
            $credentials = null;
            if ($this->shouldCreateAccount($data)) {
                $tempPassword = $this->defaultPassword();
                $email = $this->uniqueEmployeeEmail($data['full_name']);
                $user = User::create([
                    'name' => $data['full_name'],
                    'email' => $email,
                    'password' => Hash::make($tempPassword),
                ]);
                
                // Access Control panel is now the single source of truth.
                // New accounts get the baseline core permissions directly;
                // role-based assignment has been removed.
                $this->applyBaseline($user);

                $employee->update(['user_id' => $user->id]);

                $credentials = ['email' => $email, 'temp_password' => $tempPassword];
            }

            return [$employee->load(['department', 'designation', 'manager']), $credentials];
        });

        return [
            'employee' => $employee,
            'credentials' => $credentials,
        ];
    }

    /**
     * Create the login account for an employee that was added without one.
     *
     * Employees created without `create_user_account` have no `user_id`, which
     * leaves the employees list Email column blank (the column reads
     * user.email through the relation). This backfills that account using the
     * same collision-safe email and baseline grants as normal creation.
     *
     * Returns the generated credentials, or null when the employee already has
     * a valid linked user, so it is safe to call repeatedly.
     */
    public function createAccountFor(Employee $employee): ?array
    {
        if ($employee->user()->exists()) {
            return null;
        }

        return DB::transaction(function () use ($employee) {
            $tempPassword = $this->defaultPassword();
            $email = $this->uniqueEmployeeEmail($employee->full_name);

            $user = User::create([
                'name' => $employee->full_name,
                'email' => $email,
                'password' => Hash::make($tempPassword),
            ]);

            $this->applyBaseline($user);

            $employee->update(['user_id' => $user->id]);

            return ['email' => $email, 'temp_password' => $tempPassword];
        });
    }

    /**
     * Whether to provision a login for a newly created employee.
     *
     * Creating an employee has always produced a login automatically, so an
     * absent or null flag means "create". Only an explicit No (0, false, "0",
     * "false") opts out - a select left untouched must never silently skip it.
     */
    protected function shouldCreateAccount(array $data): bool
    {
        if (!array_key_exists('create_user_account', $data) || $data['create_user_account'] === null) {
            return true;
        }

        return filter_var($data['create_user_account'], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * The password assigned to a newly provisioned employee login.
     */
    protected function defaultPassword(): string
    {
        return (string) config('employees.default_password', 'password123');
    }

    /**
     * Build a collision-safe login email from the employee's name so that
     * employees sharing a name (or with whitespace/special characters) do not
     * violate the users_email_unique constraint.
     */
    protected function uniqueEmployeeEmail(string $fullName): string
    {
        // Transliterate and lowercase BEFORE stripping non-alphanumerics.
        // Applying the [^a-z0-9] pattern to a mixed-case name would replace
        // every capital letter with a separator, turning "Rohit Ambedkar" into
        // "ohit.mbedkar".
        $slug = trim(preg_replace('/[^a-z0-9]+/', '.', Str::lower(Str::ascii($fullName))), '.');
        $slug = $slug === '' ? 'employee' : $slug;

        $email = $slug . '@ronakfire.com';
        $suffix = 1;
        while (User::where('email', $email)->exists()) {
            $suffix++;
            $email = $slug . $suffix . '@ronakfire.com';
        }

        return $email;
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $this->processUploads($data);

        $employee->update($data);

        if (isset($data['designation_id']) && $employee->user) {
            // Access Control panel is now the single source of truth; ensure the
            // linked account always carries the baseline core permissions directly
            // (no role assignment).
            $this->applyBaseline($employee->user);
        }

        return $employee->fresh(['department', 'designation', 'manager']);
    }

    /**
     * Grant the baseline core permissions to a user as direct grants
     * (via the Access Control model). Only existing permissions are applied.
     */
    protected function applyBaseline(User $user): void
    {
        $baseline = config('access.baseline', []);
        $valid = \App\Models\Permission::whereIn('name', $baseline)->pluck('name')->all();
        $user->givePermissionTo($valid);
    }

    public function deleteEmployee(Employee $employee): void
    {
        $employee->delete();
    }
}

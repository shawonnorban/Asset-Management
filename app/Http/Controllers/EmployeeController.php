<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /** Where employee photos live on the public disk. */
    private const PHOTO_DIR = 'employee-photos';

    /**
     * Display the employee list
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'location', 'user']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $employees = $query->orderByDesc('id')->paginate(20);

        return Inertia::render('employees/index', [
            'employees' => $employees->getCollection()->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'name' => $employee->name,
                'image_url' => $employee->image ? Storage::url($employee->image) : null,
                'department' => $employee->department->name ?? null,
                'position' => $employee->position->name ?? null,
                'location' => $employee->location->location_name ?? null,
                'mobile' => $employee->mobile,
                'mail_address' => $employee->mail_address,
                'join_date' => optional($employee->join_date)->format('d M Y'),
                'user_email' => $employee->user?->email,
            ])->values(),
            'pagination' => $employees->toArray(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'locations' => AssetLocation::orderBy('location_name')->get(['id', 'location_name']),
            'filters' => [
                'department_id' => $request->input('department_id'),
                'location_id' => $request->input('location_id'),
            ],
            'canManage' => auth()->user()->hasPermission('employees.manage'),
        ]);
    }

    /**
     * Show one employee with the assets they hold
     */
    public function show($id)
    {
        $employee = Employee::with([
            'department',
            'position',
            'location',
            'assets.category',
            'assets.location',
            'assetAssignments.asset',
            'assetAssignments.handler',
        ])->findOrFail($id);

        return Inertia::render('employees/show', [
            'employee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'name' => $employee->name,
                'image_url' => $employee->image ? Storage::url($employee->image) : null,
                'department' => $employee->department->name ?? null,
                'position' => $employee->position->name ?? null,
                'location' => $employee->location->location_name ?? null,
                'mobile' => $employee->mobile,
                'mail_address' => $employee->mail_address,
                'join_date' => optional($employee->join_date)->format('d M Y'),
                'father_name' => $employee->father_name,
                'mother_name' => $employee->mother_name,
                'nid_number' => $employee->nid_number,
                'present_address' => $employee->present_address,
                'permanent_address' => $employee->permanent_address,
                'assets' => $employee->assets->map(fn ($asset) => [
                    'id' => $asset->id,
                    'asset_code' => $asset->asset_code,
                    'asset_name' => $asset->asset_name,
                    'category' => $asset->category->category_name ?? null,
                    'location' => $asset->location->location_name ?? null,
                    'status' => $asset->status,
                ])->values(),
                'assignments' => $employee->assetAssignments->sortByDesc('assigned_at')->map(fn ($row) => [
                    'id' => $row->id,
                    'asset_id' => $row->asset_id,
                    'asset_code' => $row->asset->asset_code ?? null,
                    'asset_name' => $row->asset->asset_name ?? null,
                    'assigned_at' => optional($row->assigned_at)->format('d M Y'),
                    'returned_at' => optional($row->returned_at)->format('d M Y'),
                    'condition' => $row->condition_on_assign,
                    'return_condition' => $row->condition_on_return,
                    'handler' => $row->handler->name ?? null,
                ])->values(),
            ],
            'canManage' => auth()->user()->hasPermission('employees.manage'),
        ]);
    }

    /**
     * Show the create employee form
     */
    public function create()
    {
        return Inertia::render('employees/form', $this->formData() + ['employee' => null]);
    }

    /**
     * Store a new employee
     */
    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $validator = Validator::make($request->all(), $this->rules() + $this->accountRules(), $this->messages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        unset($data['image']);

        $data['image'] = $this->storePhoto($request);

        $employee = Employee::create($data);
        $this->syncLoginUser($request, $employee);

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'CREATE_EMPLOYEE',
            table: 'employees',
            rowId: $employee->id,
            message: 'Added employee: ' . $employee->name,
            before: null,
            after: $employee->toArray()
        );

        return redirect()->route('employees.show', $employee->id)
            ->with('success', 'Employee created successfully');
    }

    /**
     * Show the edit employee form
     */
    public function edit($id)
    {
        return Inertia::render('employees/form', $this->formData() + [
            'employee' => Employee::with('user')->findOrFail($id),
        ]);
    }

    /**
     * Update an employee
     */
    public function update(
        Request $request,
        $id,
        AuditTrailService $auditTrailService
    ) {
        $employee = Employee::findOrFail($id);
        $before = $employee->toArray();

        $validator = Validator::make(
            $request->all(),
            $this->rules($employee->id) + $this->accountRules($employee),
            $this->messages()
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $this->deletePhoto($employee->image);
            $data['image'] = $this->storePhoto($request);
        }

        $employee->update($data);
        $this->syncLoginUser($request, $employee->fresh('user'));

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'UPDATE_EMPLOYEE',
            table: 'employees',
            rowId: $employee->id,
            message: 'Updated employee: ' . $employee->name,
            before: $before,
            after: $employee->fresh()->toArray()
        );

        return redirect()->route('employees.show', $employee->id)
            ->with('success', 'Employee updated successfully');
    }

    /**
     * Delete an employee
     * Assets are kept (employee_id becomes NULL)
     */
    public function destroy($id, AuditTrailService $auditTrailService)
    {
        $employee = Employee::findOrFail($id);
        $before = $employee->toArray();
        $name = $employee->name;
        $photo = $employee->image;

        try {
            $employee->delete();
        } catch (\Throwable $e) {
            // an open asset handover still points at this employee
            return redirect()->back()
                ->with('error', 'Failed to delete the employee. Return their assets first.');
        }

        $this->deletePhoto($photo);

        // =========================
        // AUDIT TRAIL
        // =========================
        $auditTrailService->log(
            action: 'DELETE_EMPLOYEE',
            table: 'employees',
            rowId: $before['id'],
            message: 'Deleted employee: ' . $name,
            before: $before,
            after: null
        );

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully');
    }

    /**
     * =========================
     * HELPERS
     * =========================
     */

    /** Save the uploaded photo and return its stored path. */
    private function storePhoto(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $fileName = uniqid('employee_') . '.' . $file->getClientOriginalExtension();

        return $file->storeAs(self::PHOTO_DIR, $fileName, 'public');
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Dropdown data shared by the create and edit forms. */
    private function formData(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'positions'   => Position::orderBy('name')->get(['id', 'name']),
            'locations'   => AssetLocation::orderBy('location_name')->get(['id', 'location_name']),
            'roles'       => Role::orderBy('name')->get(['id', 'name', 'role'])->map(fn ($role) => ['id' => $role->id, 'label' => $role->label])->values(),
        ];
    }

    private function rules(?int $ignoreId = null): array
    {
        $codeUnique = 'unique:employees,employee_code' . ($ignoreId ? ',' . $ignoreId : '');
        $nidUnique  = 'unique:employees,nid_number' . ($ignoreId ? ',' . $ignoreId : '');

        return [
            'employee_code'     => 'required|string|max:32|' . $codeUnique,
            'name'              => 'required|string|max:100',
            'image'             => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
            'department_id'     => 'required|exists:departments,id',
            'position_id'       => 'required|exists:positions,id',
            'location_id'       => 'nullable|exists:asset_locations,id',
            'father_name'       => 'required|string|max:100',
            'mother_name'       => 'nullable|string|max:100',
            'nid_number'        => 'nullable|string|max:30|' . $nidUnique,
            'present_address'   => 'nullable|string|max:500',
            'permanent_address' => 'nullable|string|max:500',
            'mail_address'      => 'nullable|email|max:150',
            'mobile'            => 'nullable|string|max:20',
            'join_date'         => 'nullable|date',
        ];
    }

    private function messages(): array
    {
        return [
            'employee_code.required' => 'The employee code is required',
            'employee_code.unique'   => 'That employee code is already in use',
            'name.required'          => 'The employee name is required',
            'image.image'            => 'The photo must be an image file',
            'image.max'              => 'The photo may not be larger than 4MB',
            'department_id.required' => 'Pick a department',
            'position_id.required'   => 'Pick a position',
            'father_name.required'   => "The father's name is required",
            'nid_number.unique'      => 'That NID number is already registered',
            'mail_address.email'     => 'Enter a valid mail address',
        ];
    }

    private function accountRules(?Employee $employee = null): array
    {
        if (! request()->boolean('create_user')) {
            return [];
        }

        $userId = $employee?->user?->id;
        return [
            'create_user' => 'boolean',
            'account_email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'account_role_id' => 'required|exists:roles,id',
            'account_password' => [$userId ? 'nullable' : 'required', 'min:6', 'confirmed'],
        ];
    }

    private function syncLoginUser(Request $request, Employee $employee): void
    {
        if (! $request->boolean('create_user')) {
            return;
        }

        $user = $employee->user ?: new User(['employee_id' => null]);
        $user->name = $employee->name;
        $user->email = $request->input('account_email');
        $user->role_id = $request->input('account_role_id');
        if ($request->filled('account_password')) {
            $user->password = Hash::make($request->input('account_password'));
        }
        $user->save();
        $user->syncRoles([Role::findOrFail($request->input('account_role_id'))]);
        $employee->update(['user_id' => $user->id]);
    }
}

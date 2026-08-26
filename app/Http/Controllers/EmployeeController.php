<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /** Where employee photos live on the public disk. */
    private const PHOTO_DIR = 'employee-photos';

    /**
     * Display the employee list
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'location']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        return view('employees.index', [
            'employees'   => $query->orderBy('id', 'DESC')->get(),
            'departments' => Department::orderBy('name')->get(),
            'locations'   => AssetLocation::orderBy('location_name')->get(),
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

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the create employee form
     */
    public function create()
    {
        return view('employees.create', $this->formData());
    }

    /**
     * Store a new employee
     */
    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        unset($data['image']);

        $data['image'] = $this->storePhoto($request);

        $employee = Employee::create($data);

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
        return view('employees.edit', $this->formData() + [
            'employee' => Employee::findOrFail($id),
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
            $this->rules($employee->id),
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
            'departments' => Department::orderBy('name')->get(),
            'positions'   => Position::orderBy('name')->get(),
            'locations'   => AssetLocation::orderBy('location_name')->get(),
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
}

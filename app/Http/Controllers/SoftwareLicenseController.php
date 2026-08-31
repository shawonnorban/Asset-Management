<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\SoftwareLicense;
use App\Models\SoftwareLicenseAssignment;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SoftwareLicenseController extends Controller
{
    public function index()
    {
        $licenses = SoftwareLicense::withCount([
                'assignments as seats_in_use' => fn ($q) => $q->whereNull('removed_at'),
            ])
            ->orderBy('name')
            ->get();

        return Inertia::render('software-licenses/index', [
            'title' => 'Software Licenses', 'description' => 'Track license seats and installations.',
            'rows' => $licenses->map(fn ($license) => [
                'id' => $license->id, 'name' => $license->name, 'type' => $license->license_type,
                'seats' => $license->seats_total, 'in_use' => $license->seats_in_use,
            ])->values(), 'canManage' => true,
        ]);
    }

    public function create()
    {
        return Inertia::render('software-licenses/form', [
            'title' => 'Add software license', 'record' => null,
            'licenseTypes' => SoftwareLicense::LICENSE_TYPES,
        ]);
    }

    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $validated = $request->validate($this->rules());

        $license = SoftwareLicense::create($validated);

        $auditTrailService->log(
            action: 'CREATE_SOFTWARE_LICENSE',
            table: 'software_licenses',
            rowId: $license->id,
            message: 'Added software license: ' . $license->name,
            before: null,
            after: $license->toArray()
        );

        return redirect()->route('software-licenses.index')
            ->with('success', 'Software license created successfully.');
    }

    public function show(SoftwareLicense $softwareLicense)
    {
        $softwareLicense->load(['assignments.asset.employee', 'assignments.handler']);

        return Inertia::render('software-licenses/show', [
            'title' => $softwareLicense->name,
            'license' => $softwareLicense,
        ]);
    }

    public function edit(SoftwareLicense $softwareLicense)
    {
        return Inertia::render('software-licenses/form', [
            'title' => 'Edit software license',
            'license'      => $softwareLicense,
            'licenseTypes' => SoftwareLicense::LICENSE_TYPES,
        ]);
    }

    public function update(Request $request, SoftwareLicense $softwareLicense, AuditTrailService $auditTrailService)
    {
        $validated = $request->validate($this->rules());

        $before = $softwareLicense->toArray();
        $softwareLicense->update($validated);

        $auditTrailService->log(
            action: 'UPDATE_SOFTWARE_LICENSE',
            table: 'software_licenses',
            rowId: $softwareLicense->id,
            message: 'Updated software license: ' . $softwareLicense->name,
            before: $before,
            after: $softwareLicense->fresh()->toArray()
        );

        return redirect()->route('software-licenses.index')
            ->with('success', 'Software license updated successfully.');
    }

    public function destroy(SoftwareLicense $softwareLicense, AuditTrailService $auditTrailService)
    {
        $before = $softwareLicense->toArray();
        $name = $softwareLicense->name;

        $softwareLicense->delete();

        $auditTrailService->log(
            action: 'DELETE_SOFTWARE_LICENSE',
            table: 'software_licenses',
            rowId: $before['id'],
            message: 'Deleted software license: ' . $name,
            before: $before,
            after: null
        );

        return redirect()->route('software-licenses.index')
            ->with('success', 'Software license deleted successfully.');
    }

    /**
     * =========================
     * SEAT ASSIGNMENT
     * =========================
     */

    /** Install a license on one asset, consuming a seat. */
    public function install(Request $request, Asset $asset, AuditTrailService $auditTrailService)
    {
        $validated = $request->validate([
            'software_license_id' => 'required|exists:software_licenses,id',
            'installed_at'        => 'required|date',
            'note'                => 'nullable|string|max:500',
        ]);

        $license = SoftwareLicense::findOrFail($validated['software_license_id']);

        $alreadyInstalled = SoftwareLicenseAssignment::where('software_license_id', $license->id)
            ->where('asset_id', $asset->id)
            ->whereNull('removed_at')
            ->exists();

        if ($alreadyInstalled) {
            return back()->with('error', 'That license is already installed on this asset.');
        }

        if ($license->seats_available < 1) {
            return back()->with('error', 'No seat left on that license (' . $license->seats_total . ' in use).');
        }

        $assignment = SoftwareLicenseAssignment::create($validated + [
            'asset_id'   => $asset->id,
            'handled_by' => Auth::id(),
        ]);

        $auditTrailService->log(
            action: 'INSTALL_SOFTWARE_LICENSE',
            table: 'software_license_assignments',
            rowId: $assignment->id,
            message: 'Installed ' . $license->name . ' on ' . $asset->asset_code,
            before: null,
            after: $assignment->toArray()
        );

        return back()->with('success', $license->name . ' installed on this asset.');
    }

    /** Free the seat again. */
    public function uninstall(
        Request $request,
        Asset $asset,
        SoftwareLicenseAssignment $assignment,
        AuditTrailService $auditTrailService
    ) {
        if ($assignment->asset_id !== $asset->id) {
            abort(403);
        }

        if ($assignment->removed_at) {
            return back()->with('error', 'That license was already removed from this asset.');
        }

        $before = $assignment->toArray();

        $assignment->update([
            'removed_at' => $request->input('removed_at', now()->toDateString()),
        ]);

        $auditTrailService->log(
            action: 'UNINSTALL_SOFTWARE_LICENSE',
            table: 'software_license_assignments',
            rowId: $assignment->id,
            message: 'Removed ' . ($assignment->license->name ?? '-') . ' from ' . $asset->asset_code,
            before: $before,
            after: $assignment->fresh()->toArray()
        );

        return back()->with('success', 'License removed from this asset.');
    }

    private function rules(): array
    {
        return [
            'name'          => 'required|string|max:120',
            'publisher'     => 'nullable|string|max:100',
            'version'       => 'nullable|string|max:40',
            'license_type'  => ['required', Rule::in(SoftwareLicense::LICENSE_TYPES)],
            'license_key'   => 'nullable|string|max:120',
            'seats_total'   => 'required|integer|min:1',
            'vendor'        => 'nullable|string|max:100',
            'invoice_no'    => 'nullable|string|max:60',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'expiry_date'   => 'nullable|date',
            'note'          => 'nullable|string',
        ];
    }
}

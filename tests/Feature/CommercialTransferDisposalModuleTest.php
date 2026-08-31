<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDisposal;
use App\Models\AssetLocation;
use App\Models\AssetTransfer;
use App\Models\User;
use App\Support\AssetLifecycleStatus;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommercialTransferDisposalModuleTest extends TestCase
{
    public function test_transfer_and_disposal_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('transfers.index'));
        $this->assertTrue(Route::has('transfers.create'));
        $this->assertTrue(Route::has('transfers.store'));
        $this->assertTrue(Route::has('transfers.show'));
        $this->assertTrue(Route::has('transfers.approve'));
        $this->assertTrue(Route::has('disposals.index'));
        $this->assertTrue(Route::has('disposals.create'));
        $this->assertTrue(Route::has('disposals.store'));
        $this->assertTrue(Route::has('disposals.show'));
        $this->assertTrue(Route::has('disposals.approve'));
    }

    public function test_transfer_and_disposal_statuses_cover_workflow_states(): void
    {
        $this->assertArrayHasKey('REQUESTED', AssetLifecycleStatus::TRANSFER_STATUSES);
        $this->assertArrayHasKey('APPROVED', AssetLifecycleStatus::TRANSFER_STATUSES);
        $this->assertArrayHasKey('COMPLETED', AssetLifecycleStatus::TRANSFER_STATUSES);
        $this->assertArrayHasKey('REQUESTED', AssetLifecycleStatus::DISPOSAL_STATUSES);
        $this->assertArrayHasKey('APPROVED', AssetLifecycleStatus::DISPOSAL_STATUSES);
        $this->assertArrayHasKey('DISPOSED', AssetLifecycleStatus::DISPOSAL_STATUSES);
    }

    public function test_transfer_approval_updates_asset_location_and_status(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('transfers.manage');

        $location = AssetLocation::firstOrCreate(['location_name' => 'HQ']);
        $category = AssetCategory::firstOrCreate([
            'category_name' => 'IT Equipment',
            'asset_type' => 'COMPUTER',
        ]);

        $asset = Asset::create([
            'asset_code' => 'TR-101',
            'asset_name' => 'Laptop',
            'status' => 'IN_STORAGE',
            'location_id' => $location->id,
            'category_id' => $category->id,
            'added_date' => today(),
        ]);

        $transfer = AssetTransfer::create([
            'asset_id' => $asset->id,
            'from_location_id' => $location->id,
            'to_location_id' => AssetLocation::firstOrCreate(['location_name' => 'Branch'])->id,
            'requested_by' => $user->id,
            'status' => 'REQUESTED',
            'requested_at' => today(),
        ]);

        $this->actingAs($user)
            ->post(route('transfers.approve', $transfer))
            ->assertRedirect();

        $transfer->refresh();
        $asset->refresh();

        $this->assertSame('APPROVED', $transfer->status);
        $this->assertNotNull($asset->location_id);
        $this->assertSame('IN_STORAGE', $asset->status);
    }

    public function test_disposal_approval_marks_asset_disposed(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('disposals.manage');

        $location = AssetLocation::firstOrCreate(['location_name' => 'Store']);
        $category = AssetCategory::firstOrCreate([
            'category_name' => 'Office Equipment',
            'asset_type' => 'PRINTER',
        ]);

        $asset = Asset::create([
            'asset_code' => 'DS-101',
            'asset_name' => 'Printer',
            'status' => 'IN_USE',
            'location_id' => $location->id,
            'category_id' => $category->id,
            'added_date' => today(),
        ]);

        $disposal = AssetDisposal::create([
            'asset_id' => $asset->id,
            'requested_by' => $user->id,
            'status' => 'REQUESTED',
            'reason' => 'End of life',
            'value_recovered' => 150.00,
            'requested_at' => today(),
        ]);

        $this->actingAs($user)
            ->post(route('disposals.approve', $disposal))
            ->assertRedirect();

        $disposal->refresh();
        $asset->refresh();

        $this->assertSame('DISPOSED', $disposal->status);
        $this->assertSame('DISPOSED', $asset->status);
        $this->assertNull($asset->employee_id);
    }
}

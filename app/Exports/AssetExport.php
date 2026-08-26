<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Asset::with([
                'category',
                'location',
                'employee',
                'computerSpec',
            ])
            ->orderBy('asset_code')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Asset Code',
            'Asset Name',
            'Brand',
            'Model',
            'Serial Number',
            'Category',
            'Asset Type',
            'Status',
            'Condition',
            'Location',
            'Assigned To',
            'Hostname',
            'CPU',
            'RAM (GB)',
            'Storage',
            'Operating System',
            'IP Address',
            'Vendor',
            'Purchase Date',
            'Purchase Cost',
            'Warranty End',
            'Date Added',
        ];
    }

    public function map($asset): array
    {
        $pc = $asset->computerSpec;

        return [
            $asset->asset_code,
            $asset->asset_name,
            $asset->brand ?? '-',
            $asset->model ?? '-',
            $asset->serial_number ?? '-',
            $asset->category->category_name ?? '-',
            $asset->asset_type,
            $asset->status,
            $asset->condition,
            $asset->location->location_name ?? '-',
            $asset->employee->name ?? '-',
            $pc->hostname ?? '-',
            $pc->cpu ?? '-',
            $pc->ram_gb ?? '-',
            $pc->storage_summary ?? '-',
            $pc ? trim(($pc->os ?? '') . ' ' . ($pc->os_version ?? '')) : '-',
            $pc->ip_address ?? '-',
            $asset->vendor ?? '-',
            optional($asset->purchase_date)->format('Y-m-d') ?? '-',
            $asset->purchase_cost ?? '-',
            optional($asset->warranty_end)->format('Y-m-d') ?? '-',
            optional($asset->added_date)->format('Y-m-d'),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index()
    {
        if (auth()->user()->canonicalRole() === 'employee') {
            return Inertia::render('stock/index', [
                'title' => 'Stock',
                'description' => 'Available assets ready to be assigned.',
                'assets' => collect(),
                'summary' => ['available' => 0, 'categories' => 0, 'locations' => 0],
            ]);
        }

        $inStock = fn () => Asset::whereNull('employee_id')->whereNotIn('status', ['DISPOSED', 'RETIRED']);

        $assets = $inStock()->with(['category', 'location'])->orderByDesc('id')->paginate(20);

        return Inertia::render('stock/index', [
            'title' => 'Stock',
            'description' => 'Available assets ready to be assigned.',
            'assets' => $assets->getCollection()->map(fn ($asset) => [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'asset_name' => $asset->asset_name,
                'category' => $asset->category?->category_name,
                'location' => $asset->location?->location_name,
                'condition' => $asset->condition,
                'added_date' => optional($asset->added_date)->format('d M Y'),
                'image_url' => $asset->image ? \Illuminate\Support\Facades\Storage::url($asset->image) : null,
            ])->values(),
            'pagination' => $assets->toArray(),
            // counted over the whole of stock, not just the page being shown
            'summary' => [
                'available' => $inStock()->count(),
                'categories' => $inStock()->distinct()->count('category_id'),
                'locations' => $inStock()->distinct()->count('location_id'),
            ],
        ]);
    }
}
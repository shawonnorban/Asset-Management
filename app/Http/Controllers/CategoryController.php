<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\AuditTrailService;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Make sure the user is authenticated; role middleware is handled in routes
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all categories, ordered by name
        $categories = AssetCategory::withCount('assets')->orderBy('category_name')->paginate(20);

        return Inertia::render('categories/index', [
            'title' => 'Categories',
            'description' => 'Define the asset categories used across your inventory.',
            'rows' => $categories->getCollection()->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->category_name,
                'asset_type' => AssetCategory::ASSET_TYPES[$category->asset_type] ?? $category->asset_type,
                'usage_count' => $category->assets_count,
            ])->values(),
            'pagination' => $categories->toArray(),
            'assetTypes' => AssetCategory::ASSET_TYPES,
            'canManage' => auth()->user()?->hasPermission('assets.manage') || auth()->user()?->hasPermission('categories.create') || auth()->user()?->hasPermission('categories.edit') || auth()->user()?->hasPermission('categories.update') || auth()->user()?->hasPermission('categories.delete'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('categories/form', [
            'title' => 'Add category',
            'base' => '/categories',
            'field' => 'category_name',
            'fieldLabel' => 'Category name',
            'record' => null,
            'assetTypes' => AssetCategory::ASSET_TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:50|unique:asset_categories,category_name',
            'asset_type'    => 'required|in:' . implode(',', array_keys(AssetCategory::ASSET_TYPES)),
        ], [
            'category_name.required' => 'The category name is required.',
            'category_name.max' => 'The category name may not be longer than 50 characters.',
            'category_name.unique' => 'That category name already exists.',
            'asset_type.required'  => 'Pick which kind of asset this category holds.',
        ]);

        $category = AssetCategory::create($validated);
        $auditTrailService->created('asset_categories', $category->id, $category->toArray(), 'Created category: ' . $category->category_name);

        return redirect()->route('categories.index')
                         ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetCategory $category)
    {
        return Inertia::render('categories/form', [
            'title' => 'Edit category',
            'base' => '/categories',
            'field' => 'category_name',
            'fieldLabel' => 'Category name',
            'record' => $category,
            'assetTypes' => AssetCategory::ASSET_TYPES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetCategory $category, AuditTrailService $auditTrailService)
    {
        $before = $category->toArray();
        $validated = $request->validate([
            // ignore current id for unique rule
            'category_name' => 'required|string|max:50|unique:asset_categories,category_name,' . $category->id,
            'asset_type'    => 'required|in:' . implode(',', array_keys(AssetCategory::ASSET_TYPES)),
        ], [
            'category_name.required' => 'The category name is required.',
            'category_name.max' => 'The category name may not be longer than 50 characters.',
            'category_name.unique' => 'That category name already exists.',
            'asset_type.required'  => 'Pick which kind of asset this category holds.',
        ]);

        $category->update($validated);
        $auditTrailService->updated('asset_categories', $category->id, $before, $category->fresh()->toArray(), 'Updated category: ' . $category->category_name);

        return redirect()->route('categories.index')
                         ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetCategory $category, AuditTrailService $auditTrailService)
    {
        // This fails when an FK constraint blocks it - handle with try/catch
        try {
            $before = $category->toArray();
            $category->delete();
            $auditTrailService->deleted('asset_categories', $before['id'], $before, 'Deleted category: ' . ($before['category_name'] ?? $before['id']));
            return redirect()->route('categories.index')
                             ->with('success', 'Category deleted successfully.');
        } catch (\Throwable $e) {
            // If it failed because of an FK, give an informative message
            return redirect()->route('categories.index')
                             ->with('error', 'Failed to delete the category. Make sure no asset is using it.');
        }
    }
}

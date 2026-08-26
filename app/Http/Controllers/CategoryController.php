<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;

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
        $categories = AssetCategory::withCount('assets')->orderBy('category_name')->get();

        return view('categories.index', [
            'categories' => $categories,
            'assetTypes' => AssetCategory::ASSET_TYPES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create', [
            'assetTypes' => AssetCategory::ASSET_TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

        AssetCategory::create($validated);

        return redirect()->route('categories.index')
                         ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetCategory $category)
    {
        return view('categories.edit', [
            'category'   => $category,
            'assetTypes' => AssetCategory::ASSET_TYPES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetCategory $category)
    {
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

        return redirect()->route('categories.index')
                         ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetCategory $category)
    {
        // This fails when an FK constraint blocks it - handle with try/catch
        try {
            $category->delete();
            return redirect()->route('categories.index')
                             ->with('success', 'Category deleted successfully.');
        } catch (\Throwable $e) {
            // If it failed because of an FK, give an informative message
            return redirect()->route('categories.index')
                             ->with('error', 'Failed to delete the category. Make sure no asset is using it.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InventoryCategory::with(['parent', 'children', 'inventoryItems']);
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }
        
        // Filter by parent category
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }
        
        // Sort
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);
        
        $categories = $query->paginate(25);
        $parentCategories = InventoryCategory::whereNull('parent_id')->active()->get();
        
        // Statistics
        $stats = [
            'total_categories' => InventoryCategory::count(),
            'active_categories' => InventoryCategory::active()->count(),
            'root_categories' => InventoryCategory::whereNull('parent_id')->count(),
            'categories_with_items' => InventoryCategory::has('inventoryItems')->count(),
        ];
        
        return view('inventory.categories.index', compact('categories', 'parentCategories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = InventoryCategory::whereNull('parent_id')->active()->get();
        return view('inventory.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:inventory_categories,id',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        $category = InventoryCategory::create($validated);
        
        return redirect()->route('inventory.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryCategory $category)
    {
        $category->load(['parent', 'children', 'inventoryItems' => function($query) {
            $query->with(['supplier'])->orderBy('name');
        }]);
        
        return view('inventory.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryCategory $category)
    {
        $parentCategories = InventoryCategory::whereNull('parent_id')
            ->active()
            ->where('id', '!=', $category->id)
            ->get();
            
        return view('inventory.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:inventory_categories,id',
            'sort_order' => 'nullable|integer',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);
        
        // Prevent circular reference
        if ($validated['parent_id'] == $category->id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A category cannot be its own parent.');
        }
        
        // Check if parent is a descendant (would create circular reference)
        if ($validated['parent_id']) {
            $parent = InventoryCategory::find($validated['parent_id']);
            if ($parent->allChildrenIds && in_array($category->id, $parent->allChildrenIds)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cannot set a descendant category as parent.');
            }
        }
        
        $category->update($validated);
        
        return redirect()->route('inventory.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryCategory $category)
    {
        // Check if category has inventory items
        if ($category->inventoryItems()->count() > 0) {
            return redirect()->route('inventory.categories.index')
                ->with('error', 'Cannot delete category that has inventory items. Please reassign items first.');
        }
        
        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->route('inventory.categories.index')
                ->with('error', 'Cannot delete category that has sub-categories. Please delete or reassign sub-categories first.');
        }
        
        $category->delete();
        
        return redirect()->route('inventory.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
    
    /**
     * Get categories for API/select options
     */
    public function getCategories(Request $request)
    {
        $query = InventoryCategory::active();
        
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }
        
        $categories = $query->orderBy('name')->get();
        
        return response()->json($categories);
    }
}
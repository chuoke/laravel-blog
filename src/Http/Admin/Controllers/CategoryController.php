<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Actions\CategoryList;
use Chuoke\Blog\Actions\CategoryCreate;
use Chuoke\Blog\Actions\CategoryUpdate;
use Chuoke\Blog\Actions\CategoryDelete;
use Chuoke\Blog\Dtos\CategoryCreateData;
use Chuoke\Blog\Dtos\CategoryUpdateData;

class CategoryController extends Controller
{
    public function index(CategoryList $action)
    {
        return Inertia::render('Blog/Admin/Categories/Index', [
            'categories' => $action->execute(),
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function store(Request $request, CategoryCreate $action)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);

        $data = new CategoryCreateData(
            name: $validated['name'],
            description: $validated['description'] ?? null,
            parentId: $validated['parent_id'] ?? null,
            sortOrder: (int) ($validated['sort_order'] ?? 0),
        );

        $action->execute($data);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category, CategoryUpdate $action)
    {
        $validated = $request->validate([
            'name' => 'nullable|array',
            'name.*' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);

        $data = new CategoryUpdateData(
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            parentId: $validated['parent_id'] ?? null,
            sortOrder: isset($validated['sort_order']) ? (int) $validated['sort_order'] : null,
        );

        $action->execute($category, $data);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category, CategoryDelete $action)
    {
        $action->execute($category);
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}

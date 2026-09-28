<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Chuoke\Blog\Actions\CategoryCreate;
use Chuoke\Blog\Actions\CategoryDelete;
use Chuoke\Blog\Actions\CategoryList;
use Chuoke\Blog\Actions\CategoryUpdate;
use Chuoke\Blog\Dtos\CategoryCreateData;
use Chuoke\Blog\Dtos\CategoryUpdateData;
use Chuoke\Blog\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(CategoryList $action)
    {
        return Inertia::render('Blog/Admin/Categories/Index', [
            'categories' => $action->execute(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Admin/Categories/Form', [
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function store(Request $request, CategoryCreate $action)
    {
        $locales = config('blog.supported_locales', ['en' => 'English']);
        $defaultLocale = array_key_first($locales);
        $localeKeys = implode(',', array_keys($locales));

        $validated = $request->validate([
            'name' => 'required|array:'.$localeKeys,
            'name.'.$defaultLocale => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
            'description' => 'nullable|array:'.$localeKeys,
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

        $category = $action->execute($data);

        return redirect()->route('blog.admin.categories.edit', $category)
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Blog/Admin/Categories/Form', [
            'category' => $category,
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function update(Request $request, Category $category, CategoryUpdate $action)
    {
        $locales = config('blog.supported_locales', ['en' => 'English']);
        $defaultLocale = array_key_first($locales);
        $localeKeys = implode(',', array_keys($locales));

        $validated = $request->validate([
            'name' => 'nullable|array:'.$localeKeys,
            'name.'.$defaultLocale => 'required_with:name|string|max:255',
            'name.*' => 'nullable|string|max:255',
            'description' => 'nullable|array:'.$localeKeys,
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

        return redirect()->route('blog.admin.categories.edit', $category)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category, CategoryDelete $action)
    {
        $action->execute($category);

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}

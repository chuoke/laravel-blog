<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Chuoke\Blog\Models\Tag;
use Chuoke\Blog\Actions\TagList;
use Chuoke\Blog\Actions\TagCreate;
use Chuoke\Blog\Actions\TagUpdate;
use Chuoke\Blog\Actions\TagDelete;
use Chuoke\Blog\Dtos\TagCreateData;
use Chuoke\Blog\Dtos\TagUpdateData;

class TagController extends Controller
{
    public function index(TagList $action)
    {
        return Inertia::render('Blog/Admin/Tags/Index', [
            'tags' => $action->execute(),
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function store(Request $request, TagCreate $action)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $data = new TagCreateData(
            name: $validated['name'],
        );

        $action->execute($data);

        return redirect()->back()->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, Tag $tag, TagUpdate $action)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $data = new TagUpdateData(
            name: $validated['name'],
        );

        $action->execute($tag, $data);

        return redirect()->back()->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag, TagDelete $action)
    {
        $action->execute($tag);
        return redirect()->back()->with('success', 'Tag deleted successfully.');
    }
}

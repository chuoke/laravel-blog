<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Chuoke\Blog\Actions\TagCreate;
use Chuoke\Blog\Actions\TagDelete;
use Chuoke\Blog\Actions\TagList;
use Chuoke\Blog\Actions\TagUpdate;
use Chuoke\Blog\Dtos\TagCreateData;
use Chuoke\Blog\Dtos\TagUpdateData;
use Chuoke\Blog\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index(TagList $action)
    {
        return Inertia::render('Blog/Admin/Tags/Index', [
            'tags' => $action->execute(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Admin/Tags/Form', [
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function store(Request $request, TagCreate $action)
    {
        $locales = config('blog.supported_locales', ['en' => 'English']);
        $defaultLocale = array_key_first($locales);
        $localeKeys = implode(',', array_keys($locales));

        $validated = $request->validate([
            'name' => 'required|array:'.$localeKeys,
            'name.'.$defaultLocale => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
        ]);

        $data = new TagCreateData(
            name: $validated['name'],
        );

        $tag = $action->execute($data);

        return redirect()->route('blog.admin.tags.edit', $tag)
            ->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag)
    {
        return Inertia::render('Blog/Admin/Tags/Form', [
            'tag' => $tag,
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function update(Request $request, Tag $tag, TagUpdate $action)
    {
        $locales = config('blog.supported_locales', ['en' => 'English']);
        $defaultLocale = array_key_first($locales);
        $localeKeys = implode(',', array_keys($locales));

        $validated = $request->validate([
            'name' => 'required|array:'.$localeKeys,
            'name.'.$defaultLocale => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
        ]);

        $data = new TagUpdateData(
            name: $validated['name'],
        );

        $action->execute($tag, $data);

        return redirect()->route('blog.admin.tags.edit', $tag)
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag, TagDelete $action)
    {
        $action->execute($tag);

        return redirect()->back()->with('success', 'Tag deleted successfully.');
    }
}

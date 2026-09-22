<?php

namespace Chuoke\Blog\Http\Api\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Chuoke\Blog\Models\Tag;
use Chuoke\Blog\Actions\TagGet;
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
        return $action->execute();
    }

    public function store(Request $request, TagCreate $action)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $data = new TagCreateData(name: $validated['name']);

        return response()->json($action->execute($data), 201);
    }

    public function show(Tag $tag, TagGet $action)
    {
        return $action->execute($tag);
    }

    public function update(Request $request, Tag $tag, TagUpdate $action)
    {
        $validated = $request->validate([
            'name' => 'nullable|array',
            'name.*' => 'nullable|string|max:255',
        ]);

        $data = new TagUpdateData(name: $validated['name'] ?? null);

        return response()->json($action->execute($tag, $data));
    }

    public function destroy(Tag $tag, TagDelete $action)
    {
        $action->execute($tag);
        return response()->noContent();
    }
}

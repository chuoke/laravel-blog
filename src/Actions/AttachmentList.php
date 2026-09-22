<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Attachment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttachmentList
{
    public function execute(int $perPage = 20, ?string $type = null): LengthAwarePaginator
    {
        $query = Attachment::query();

        if ($type !== null) {
            $query->where('type', $type);
        }

        $query->latest('id');

        return $query->paginate($perPage);
    }
}

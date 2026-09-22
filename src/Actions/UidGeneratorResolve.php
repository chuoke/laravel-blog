<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Support\Nanoid;

class UidGeneratorResolve
{
    public function execute()
    {
        $generatorClass = config('blog.uid.generator.class', Nanoid::class);
        $config = config('blog.uid.generator.config', []);

        return new $generatorClass(...$config);
    }
}
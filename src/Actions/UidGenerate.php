<?php

namespace Chuoke\Blog\Actions;

class UidGenerate
{
    public function execute(): string
    {
        $generator = app(UidGeneratorResolve::class)->execute();

        return $generator->generate();
    }
}


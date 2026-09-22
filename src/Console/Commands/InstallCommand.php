<?php

namespace Chuoke\Blog\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'blog:install {--stack=inertia : The development stack that should be installed (currently only supports "inertia")} {--force : Overwrite any existing files}';

    protected $description = 'Install the Blog package frontend scaffolding';

    public function handle()
    {
        $stack = $this->option('stack');

        if ($stack === 'inertia') {
            $this->installInertiaStack();
        } else {
            $this->error("Invalid stack '{$stack}' specified. Supported stacks are: inertia");
            return 1;
        }
    }

    protected function installInertiaStack()
    {
        $this->info('Installing Blog Inertia/Vue 3 Scaffolding...');

        foreach (['blog-config', 'blog-migrations', 'blog-assets'] as $tag) {
            $this->call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $this->option('force'),
            ]);
        }

        $this->info('Installation complete!');
        $this->line('Please make sure your host application has Vue 3, Tailwind CSS, and Inertia.js configured.');
    }
}

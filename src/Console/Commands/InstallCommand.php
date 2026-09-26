<?php

namespace Chuoke\Blog\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'blog:install {--stack=inertia : The development stack that should be installed (currently only supports "inertia")} {--force : Overwrite any existing files}';

    protected $description = 'Install the Blog package frontend scaffolding';

    public function handle(): int
    {
        $stack = $this->option('stack');

        if ($stack === 'inertia') {
            $this->installInertiaStack();

            return self::SUCCESS;
        } else {
            $this->error("Invalid stack '{$stack}' specified. Supported stacks are: inertia");

            return self::FAILURE;
        }
    }

    protected function installInertiaStack(): void
    {
        $this->info('Installing Blog Inertia/Vue 3 Scaffolding...');

        foreach (['blog-config', 'blog-migrations', 'blog-assets'] as $tag) {
            $this->call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $this->option('force'),
            ]);
        }

        $this->info('Installation complete!');
        $this->line('Install the required frontend packages: npm install md-editor-v3 vue-i18n');
        $this->line('Then register the published blog i18n messages in your Inertia entry. See the package README for the integration snippet.');
    }
}

<?php

declare(strict_types=1);

namespace PreemStudio\Playbooks;

use PreemStudio\Playbooks\Commands\RunPlaybookCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-playbooks')
            ->hasCommand(RunPlaybookCommand::class);
    }
}

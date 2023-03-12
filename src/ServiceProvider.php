<?php

declare(strict_types=1);

namespace PreemStudio\Playbooks;

use PreemStudio\Playbooks\Commands\RunPlaybookCommand;
use PreemStudio\Jetpack\Package\AbstractServiceProvider;
use PreemStudio\Jetpack\Package\Package;

class ServiceProvider extends AbstractServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-playbooks')
            ->hasCommand(RunPlaybookCommand::class);
    }
}

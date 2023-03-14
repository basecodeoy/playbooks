<?php

declare(strict_types=1);

namespace Tests;

use PreemStudio\Jetpack\TestBench\AbstractPackageTestCase;

abstract class TestCase extends AbstractPackageTestCase
{
    public function getServiceProviderClass(): string
    {
        return \PreemStudio\Playbooks\ServiceProvider::class;
    }
}

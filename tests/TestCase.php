<?php

declare(strict_types=1);

namespace Tests;

use BaseCodeOy\PackagePowerPack\TestBench\AbstractPackageTestCase;

/**
 * @internal
 */
abstract class TestCase extends AbstractPackageTestCase
{
    public function getServiceProviderClass(): string
    {
        return \BaseCodeOy\Playbooks\ServiceProvider::class;
    }
}

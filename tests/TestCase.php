<?php

declare(strict_types=1);

namespace Tests;

use BombenProdukt\PackagePowerPack\TestBench\AbstractPackageTestCase;

/**
 * @internal
 */
abstract class TestCase extends AbstractPackageTestCase
{
    public function getServiceProviderClass(): string
    {
        return \BombenProdukt\Playbooks\ServiceProvider::class;
    }
}

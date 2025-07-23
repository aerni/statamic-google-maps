<?php

namespace Aerni\GoogleMaps\Tests;

use Aerni\GoogleMaps\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}

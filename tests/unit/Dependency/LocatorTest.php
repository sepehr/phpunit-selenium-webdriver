<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Sepehr\PHPUnitSelenium\Util\Locator;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

/**
 * Here we're testing the creation of a hard dependency. Even though we
 * could easily inject a mocked copy of the dependency class into the SUT,
 * we went the hard way and used aliased/overloaded mocks in few test methods,
 * to actually test the creation of dependency class, when no instance is injected.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LocatorTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAnInstance()
    {
        $this->mock('alias:' . Locator::class)
            ->shouldReceive('create')
            ->once()
            ->andReturn(Mockery::self());

        $this->assertInstanceOf(Locator::class, $this->locator());
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $this->inject(Locator::class)
            ->shouldNotReceive('create');

        $this->assertInstanceOf(Locator::class, $this->locator());
    }
}

<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Sepehr\PHPUnitSelenium\WebDriver\WebDriverBy;
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
class WebDriverByTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAnInstanceWithoutAnySpecificMechanism()
    {
        $this->mock('alias:' . WebDriverBy::class)
            ->shouldReceive('create')
            ->once()
            ->with(null, null)
            ->andReturn(Mockery::self());

        $this->assertInstanceOf(WebDriverBy::class, $this->webDriverBy());
    }

    /** @test */
    public function createsAnInstanceWithAMechanismAndAValue()
    {
        $this->mock('alias:' . WebDriverBy::class)
            ->shouldReceive('create')
            ->once()
            ->with($mechanism = 'issd', $value = 'someId')
            ->andReturn(Mockery::self());

        $this->assertInstanceOf(WebDriverBy::class, $this->webDriverBy($mechanism, $value));
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $this->inject(WebDriverBy::class)
            ->shouldNotReceive('create');

        $this->assertInstanceOf(WebDriverBy::class, $this->webDriverBy());
    }
}

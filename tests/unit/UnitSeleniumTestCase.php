<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Mockery\MockInterface;
use phpmock\mockery\PHPMockery;
use Mockery\CompositeExpectation;
use Sepehr\PHPUnitSelenium\SeleniumTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Base class for all unit tests.
 *
 * This may seem a little bit complicated, and in fact, it is! But in return
 * it provides an easy-to-use API to write unit tests as fast as possible. For
 * example, let's inject a dependency mock into the SUT:
 *
 *     $this->inject($this->mock(RemoteWebDriver::class));
 *
 * Or even better:
 *
 *     $this->inject(
 *         $this->mock('overload:' . DesiredCapabilities::class)
 *             ->shouldReceive('create')
 *             ->once()
 *     );
 *
 * How cool is that... Ahh, is that cool at all?!
 */
abstract class UnitSeleniumTestCase extends SeleniumTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * An array of reusable dependency mocks.
     *
     * @var MockInterface[]
     */
    protected $mocks = [];

    /**
     * Test setup.
     */
    public function setUp()
    {
        // Mock all calls to sleep() during unit tests
        PHPMockery::mock('Sepehr\PHPUnitSelenium', 'sleep')
            ->zeroOrMoreTimes()
            ->andReturn(0);

        // Setup RemoteWebDriver aliased mock
        $this->mock('alias:' . RemoteWebDriver::class, function ($mock) {
            // Satisfy the expectation set by PHPUnit's @after annotation, by default.
            return $mock->shouldReceive('quit');
        });

        parent::setUp();
    }

    /**
     * Manages mocks dependency array.
     *
     * @param string $mockId
     * @param \Closure|null $closure
     *
     * @return MockInterface
     * @throws \Exception
     */
    protected function mock($mockId, $closure = null)
    {
        if (key_exists($mockId, $this->mocks)) {
            return $closure
                ? $closure($this->mocks[$mockId])
                : $this->mocks[$mockId];
        }

        try {
            return $this->mocks[$mockId] = $closure
                ? Mockery::mock($mockId, $closure)
                : Mockery::mock($mockId);
        } catch (\Exception $e) {
            throw new \Exception("Mock object could not be found/created with the identifier: $mockId");
        }
    }

    /**
     * Genius mock injector; she knows how to inject a mock.
     *
     * @param MockInterface|CompositeExpectation|string $mock
     *
     * @return MockInterface
     */
    protected function inject($mock)
    {
        $mock = $this->normalizeMock($mock);

        $injector = $this->getDependencyInjector($mock);

        // Each dependency might have its own logic for
        // injection, so the separate methods...
        return $this->$injector($mock);
    }

    /**
     * Injects a mocked RemoteWebDriver into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $mock
     *
     * @return RemoteWebDriver
     */
    protected function injectWebDriver($mock = RemoteWebDriver::class)
    {
        return $this->injectDependency($mock, 'setWebDriver');
    }

    /**
     * Injects a mocked DesiredCapabilities into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $mock
     *
     * @return DesiredCapabilities
     */
    protected function injectDesiredCapabilities($mock = DesiredCapabilities::class)
    {
        return $this->injectDependency($mock, 'setDesiredCapabilities');
    }

    /**
     * Injects a mocked dependency into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $mock
     * @param string|null $setter
     *
     * @return MockInterface
     */
    protected function injectDependency($mock, $setter = null)
    {
        $mock   = $this->normalizeMock($mock);
        $setter = $setter ? $setter : $this->getDependencySetter($mock);

        $this->$setter($mock);

        return $mock;
    }

    /**
     * Returns setter method name from a dependency mock object.
     *
     * @param MockInterface $mock
     *
     * @return string
     */
    private function getDependencySetter($mock)
    {
        return $this->getDependencyMethodName($mock, 'set');
    }

    /**
     * Returns injector method name from a dependency mock object.
     *
     * @param MockInterface $mock
     *
     * @return string
     */
    private function getDependencyInjector($mock)
    {
        return $this->getDependencyMethodName($mock, 'inject');
    }

    /**
     * Returns setter/injector method name for a dependency mock.
     *
     * @param MockInterface $mock
     * @param string $type
     *
     * @return string
     * @throws \Exception
     */
    private function getDependencyMethodName(MockInterface $mock, $type)
    {
        $fqn    = $this->getDependencyFqn($mock);
        $method = $type . $this->getDependencyName($this->getDependencyClass($fqn));

        if (method_exists($this, $method)) {
            return $method;
        }

        throw new \Exception("Could not find the \"$type\" method for: " . get_class($mock));
    }

    /**
     * Returns FQN of the mocked object.
     *
     * This is too much, I know :/
     *
     * @param MockInterface $mock
     *
     * @return string
     * @throws \Exception
     */
    private function getDependencyFqn($mock)
    {
        $fqn = preg_replace(
            '/^Mockery\\\(\d+)\\\/',
            '',
            str_replace('_', '\\', get_class($mock))
        );

        if (class_exists($fqn)) {
            return $fqn;
        }

        throw new \Exception('Could not extract the FQN of original class from mock: ' . get_class($mock));
    }

    /**
     * Returns the short class name for a FQN.
     *
     * @param string $fqn
     *
     * @return string
     */
    private function getDependencyClass($fqn)
    {
        return ltrim(strrchr($fqn, '\\'), '\\');
    }

    /**
     * Returns the dependency name that SUT uses.
     *
     * @param string $dependencyClass
     *
     * @return string
     */
    private function getDependencyName($dependencyClass)
    {
        $exceptions = [
            'RemoteWebDriver' => 'WebDriver',
        ];

        return key_exists($dependencyClass, $exceptions)
            ? $exceptions[$dependencyClass]
            : $dependencyClass;
    }

    /**
     * Normalizes a mock object.
     *
     * @param MockInterface|CompositeExpectation|string $mock
     *
     * @return MockInterface
     * @throws \Exception
     */
    private function normalizeMock($mock)
    {
        if (is_string($mock)) {
            $mock = $this->mock($mock);
        } elseif ($mock instanceof CompositeExpectation) {
            $mock = $mock->getMock();
        }

        if (! $mock instanceof MockInterface) {
            throw new \Exception('Cannot inject an invalid mock man, what the fuck?!');
        }

        return $mock;
    }
}

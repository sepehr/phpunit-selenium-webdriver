<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Mockery\MockInterface;
use phpmock\mockery\PHPMockery;
use Mockery\CompositeExpectation;
use Sepehr\PHPUnitSelenium\Util\Locator;
use Sepehr\PHPUnitSelenium\Util\Filesystem;
use Sepehr\PHPUnitSelenium\SeleniumTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Sepehr\PHPUnitSelenium\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Base class for all unit tests that deal with test doubles.
 *
 * This may seem a little bit complicated, and in fact, it is! But in return
 * it provides an easy-to-use API to write unit tests as fast as possible. For
 * example, let's inject a dependency mock into the SUT:
 *
 *     $this->inject($this->spy(RemoteWebDriver::class));
 *
 * Or better:
 *
 *     $this->inject(
 *         $this->mock('overload:' . DesiredCapabilities::class)
 *             ->shouldReceive('create')
 *             ->once()
 *     );
 *
 * Or even better:
 *
 *     $this->inject(WebDriverBy::class)
 *         ->shouldReceive('create')
 *         ->once()
 *         ->withAnyArgs()
 *         ->andReturn(Mockery::slef());
 *
 * How cool is that?!
 */
abstract class UnitSeleniumTestCase extends SeleniumTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * An array of reusable dependency test doubles.
     *
     * @var MockInterface[]
     */
    protected $doubles = [];

    /**
     * Test setup.
     */
    public function setUp()
    {
        // Mock all calls to sleep() during unit tests
        PHPMockery::mock('Sepehr\PHPUnitSelenium', 'sleep')
            ->zeroOrMoreTimes()
            ->andReturn(0);

        parent::setUp();
    }

    /**
     * Manages test doubles.
     *
     * @param string $doubleId
     * @param \Closure|null $closure
     * @param string $doubleType
     *
     * @return MockInterface
     * @throws \Exception
     */
    protected function double($doubleId, $closure = null, $doubleType = 'mock')
    {
        if (key_exists($doubleId, $this->doubles)) {
            return $closure
                ? $closure($this->doubles[$doubleId])
                : $this->doubles[$doubleId];
        }

        try {
            return $this->doubles[$doubleId] = $closure
                ? Mockery::$doubleType($doubleId, $closure)
                : Mockery::$doubleType($doubleId);
        } catch (\Exception $e) {
            throw new \Exception(
                "Could not find/create a $doubleType with identifier: $doubleId\nMessage: {$e->getMessage()}"
            );
        }
    }

    /**
     * Mock interface.
     *
     * @param string $mockId
     * @param \Closure|null $closure
     *
     * @return MockInterface
     * @throws \Exception
     */
    protected function mock($mockId, $closure = null)
    {
        return $this->double($mockId, $closure, 'mock');
    }

    /**
     * Spy interface.
     *
     * @param string $spyId
     * @param \Closure|null $closure
     *
     * @return MockInterface
     * @throws \Exception
     */
    protected function spy($spyId, $closure = null)
    {
        return $this->double($spyId, $closure, 'spy');
    }

    /**
     * Genius test double injector; she knows how to inject!
     *
     * @param MockInterface|CompositeExpectation|string $double
     * @param string $doubleType
     *
     * @return MockInterface
     */
    protected function inject($double, $doubleType = 'mock')
    {
        $double = $this->normalizeDouble($double, $doubleType);

        $injector = $this->getDependencyInjector($double);

        // Each dependency might have its own logic for
        // injection, so the separate methods...
        return $this->$injector($double);
    }

    /**
     * Injects a mock.
     *
     * @param MockInterface|CompositeExpectation|string $double
     *
     * @return MockInterface
     */
    protected function injectMock($double)
    {
        return $this->inject($double, 'mock');
    }

    /**
     * Injects a spy.
     *
     * @param MockInterface|CompositeExpectation|string $double
     *
     * @return MockInterface
     */
    protected function injectSpy($double)
    {
        return $this->inject($double, 'spy');
    }

    /**
     * Injects a RemoteWebDriver double into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $double
     *
     * @return RemoteWebDriver
     */
    protected function injectWebDriver($double = RemoteWebDriver::class)
    {
        // Default behavior for mock doubles
        $double = $this->normalizeDouble($double)->shouldReceive('quit')->byDefault();

        return $this->injectDependency($double, 'setWebDriver');
    }

    /**
     * Injects a DesiredCapabilities double into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $double
     *
     * @return DesiredCapabilities
     */
    protected function injectDesiredCapabilities($double = DesiredCapabilities::class)
    {
        return $this->injectDependency($double, 'setDesiredCapabilities');
    }

    /**
     * Injects a WebDriverBy double into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $double
     *
     * @return WebDriverBy
     */
    protected function injectWebDriverBy($double = WebDriverBy::class)
    {
        return $this->injectDependency($double, 'setWebDriverBy');
    }

    /**
     * Injects a Filesystem double into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $double
     *
     * @return Filesystem
     */
    protected function injectFilesystem($double = Filesystem::class)
    {
        return $this->injectDependency($double, 'setFilesystem');
    }

    /**
     * Injects a Locator double into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $double
     *
     * @return Filesystem
     */
    protected function injectLocator($double = Locator::class)
    {
        return $this->injectDependency($double, 'setLocator');
    }

    /**
     * Injects a dependency double into the SeleniumTestCase.
     *
     * @param MockInterface|CompositeExpectation|string $double
     * @param string|null $setter
     *
     * @return MockInterface
     */
    protected function injectDependency($double, $setter = null)
    {
        $double   = $this->normalizeDouble($double);
        $setter = $setter ? $setter : $this->getDependencySetter($double);

        $this->$setter($double);

        return $double;
    }

    /**
     * Returns setter method name from a dependency mock object.
     *
     * @param MockInterface $double
     *
     * @return string
     */
    private function getDependencySetter($double)
    {
        return $this->getDependencyMethodName($double, 'set');
    }

    /**
     * Returns injector method name from a dependency mock object.
     *
     * @param MockInterface $double
     *
     * @return string
     */
    private function getDependencyInjector($double)
    {
        return $this->getDependencyMethodName($double, 'inject');
    }

    /**
     * Returns setter/injector method name for a dependency mock.
     *
     * @param MockInterface $double
     * @param string $type
     *
     * @return string
     * @throws \Exception
     */
    private function getDependencyMethodName(MockInterface $double, $type)
    {
        $fqn    = $this->getDependencyFqn($double);
        $method = $type . $this->getDependencyName($this->getDependencyClass($fqn));

        if (method_exists($this, $method)) {
            return $method;
        }

        throw new \Exception("Could not find the \"$type\" method for: " . get_class($double));
    }

    /**
     * Returns FQN of the test double object.
     *
     * This is too much, I know :/
     *
     * @param MockInterface $double
     *
     * @return string
     * @throws \Exception
     */
    private function getDependencyFqn($double)
    {
        $fqn = preg_replace(
            '/^Mockery\\\(\d+)\\\/',
            '',
            str_replace('_', '\\', get_class($double))
        );

        if (class_exists($fqn)) {
            return $fqn;
        }

        throw new \Exception('Could not extract the FQN of original class from mock: ' . get_class($double));
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
     * Normalizes a test double object.
     *
     * @param MockInterface|CompositeExpectation|string $double
     * @param string $doubleType
     *
     * @return MockInterface
     * @throws \Exception
     */
    private function normalizeDouble($double, $doubleType = 'mock')
    {
        if (is_string($double)) {
            $double = $this->$doubleType($double);
        } elseif ($double instanceof CompositeExpectation) {
            $double = $double->getMock();
        }

        if (! $double instanceof MockInterface) {
            throw new \Exception('Cannot inject an invalid test double, what the fuck?!');
        }

        return $double;
    }
}

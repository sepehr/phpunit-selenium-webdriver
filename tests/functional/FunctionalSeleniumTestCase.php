<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

use Sepehr\PHPUnitSelenium\SeleniumTestCase;

abstract class FunctionalSeleniumTestCase extends SeleniumTestCase
{

    /**
     * Holds test file title.
     *
     * @var string
     */
    protected $testFileTitle = 'Test page for phpunit-selenium-webdriver';

    /**
     * Browser name.
     *
     * @var string
     */
    protected $browser = 'firefox';

    /**
     * Returns the full path to html test file.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getTestFilePath($path)
    {
        return __DIR__ . "/html/$path";
    }

    /**
     * Returns the URL to html test file.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getTestFileUrl($path)
    {
        return 'file:///' . $this->getTestFilePath($path);
    }

    /**
     * Visits a test file.
     *
     * @param string $path
     *
     * @return $this
     */
    protected function visitTestFile($path = 'test.html')
    {
        return $this->visit($this->getTestFileUrl($path));
    }
}

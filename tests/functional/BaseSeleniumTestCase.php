<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

use Sepehr\PHPUnitSelenium\SeleniumTestCase;

abstract class BaseSeleniumTestCase extends SeleniumTestCase
{

    /**
     * Browser name.
     *
     * @var string
     */
    protected $browser = 'firefox';

    /**
     * Returns the URL to html test file.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getTestFileUrl($path)
    {
        return 'file:///' . __DIR__ . "/html/$path";
    }
}

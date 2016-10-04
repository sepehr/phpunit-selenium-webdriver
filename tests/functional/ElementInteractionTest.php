<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ElementInteractionTest extends FunctionalSeleniumTestCase
{

    /** @test */
    public function typesIntoAnElement()
    {
        $this->visitTestFile()
            ->type($expected = 'Life is a magnificent illusion', $target = 'findMeByName');

        $this->assertSame(
            $expected,
            $this->findByName($target)->getAttribute('value')
        );
    }
}

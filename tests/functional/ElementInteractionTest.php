<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ElementInteractionTest extends FunctionalSeleniumTestCase
{

    /** @test */
    public function typesIntoAnInputElement()
    {
        $this->visitTestFile()
            ->type($expected = 'Life is a magnificent illusion...', $target = 'typeIntoMe');

        $this->assertSame(
            $expected,
            $this->findById($target)->getAttribute('value')
        );
    }

    /** @test */
    public function clicksOnANonLinkElement()
    {
        $this->visitTestFile()->click($target = 'onClickChange');

        $this->assertSame(
            'Oh, you clicker!',
            $this->findById($target)->getText()
        );
    }

    /** @test */
    public function clicksOnALinkAnchor()
    {
        $this->visitTestFile()->click('SeleniumHQ');

        $this->assertSame('http://www.seleniumhq.org', $this->url());
    }

    /** @test */
    public function clearsAnInput()
    {
        $this->visitTestFile()
            ->type('Something I want to clear right now!', $target = 'findMeByName')
            ->clear($target);

        $this->assertSame('', $this->findByName($target)->getAttribute('value'));
    }

    /** @test */
    public function hitsKeysOnElements()
    {
        $this->visitTestFile()->hit('enter', $target = 'hitEnterOnMe');

        $this->assertSame('', $this->findById($target)->getAttribute('value'));
    }
}

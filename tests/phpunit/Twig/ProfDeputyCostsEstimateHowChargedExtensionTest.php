<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Report\Report;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class ProfDeputyCostsEstimateHowChargedExtensionTest extends TestCase
{
    /** @var ProfDeputyCostsEstimateHowChargedExtension */
    private $sut;

    /** @var TranslatorInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $translator;

    public function setUp()
    {
        $this->translator = $this->getMock(TranslatorInterface::class);
        $this->sut = new ProfDeputyCostsEstimateHowChargedExtension($this->translator);
    }

    public function testGetFunctionsRegistersTwigFunctions()
    {
        $registered = $this->sut->getFunctions();

        $this->assertInstanceOf(\Twig_SimpleFunction::class, $registered[0]);
        $this->assertEquals('howCharged', $registered[0]->getName());
        $this->assertEquals('renderHowCharged', $registered[0]->getCallable()[1]);
    }

    /**
     * @dataProvider getHowChargedVariations
     * @param $howCharged
     * @param $translatorPath
     * @param $translatedResult
     */
    public function testRenderHowChargedTwigFunction($howCharged, $translatorPath, $translatedResult)
    {
        $this->ensureTranslatorIsInvokedCorrectly($translatorPath, $translatedResult);

        $result = $this->sut->renderHowCharged($howCharged);
        $this->assertEquals($translatedResult, $result);
    }

    /**
     * @return array
     */
    public function getHowChargedVariations()
    {
        return [
            [
                'howCharged' => Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED,
                'translatorPath' => 'howCharged.form.options.fixed',
                'translatedResult' => 'Fixed Costs'
            ],
            [
                'howCharged' => Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_ASSESSED,
                'translatorPath' => 'howCharged.form.options.assessed',
                'translatedResult' => 'Assessed Costs'
            ],
            [
                'howCharged' => Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_BOTH,
                'translatorPath' => 'howCharged.form.options.both',
                'translatedResult' => 'Both Costs'
            ]
        ];
    }

    /**
     * @param $translatorPath
     * @param $translatedResult
     */
    private function ensureTranslatorIsInvokedCorrectly($translatorPath, $translatedResult)
    {
        $this
            ->translator
            ->expects($this->once())
            ->method('trans')
            ->with($translatorPath, [], 'report-prof-deputy-costs-estimate')
            ->willReturn($translatedResult);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionOnUnrecognisedHowChargedArgument()
    {
        $this->sut->renderHowCharged('unexpected-arg');
    }
}

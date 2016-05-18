<?php

namespace AppBundle\Resources\views\Report\Formatted;

use AppBundle\Resources\views\Report\AbstractReportTest;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class SafeguardingTest extends AbstractReportTest
{
    private $templateName = 'AppBundle:Report:Formatted/_safeguarding.html.twig';

    public function testReportContainsSafeguardingSection()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('yes')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertSectionContainsText($crawler, '#safeguarding-section', 'Section 4');
        $this->assertSectionContainsText($crawler, '#safeguarding-section', 'Safeguarding');
    }

    public function testShowThatILiveWithClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('yes')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertSectionContainsText($crawler, '#safeguarding-section', 'Do you live with the client?');
        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'Do you live with the client?', 'yes');
    }

    public function testShowThatIDontLiveWithClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertSectionContainsText($crawler, '#safeguarding-section', 'Do you live with the client?');
        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'Do you live with the client?', 'no');
    }

    public function testDontShowIntervalAnswersIfLivingWithClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('yes')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);
        $safeguardSection = $crawler->filter('#safeguarding-section')->eq(0);

        $this->assertSectionDoesntExist($safeguardSection, '#safeguarding-visits-subsection');
        $this->assertSectionDoesntExist($safeguardSection, '#safeguarding-visitors-subsection');
        $this->assertSectionDoesntExist($safeguardSection, '#safeguarding-furtherinfo-subsection');
    }

    public function testShowIntervalAnswersIfLivingWithClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getHowOftenDoYouVisit')->andReturn('everyday')
            ->shouldReceive('getHowOftenDoYouPhoneOrVideoCall')->andReturn('everday')
            ->shouldReceive('getHowOftenDoYouWriteEmailOrLetter')->andReturn('everyday')
            ->shouldReceive('getHowOftenDoesClientSeeOtherPeople')->andReturn('everyday')
            ->shouldReceive('getAnythingElseToTell')->andReturn('nothing else to tell you')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);
        $safeguardSection = $crawler->filter('#safeguarding-section')->eq(0);

        $this->assertSectionDoesExist($safeguardSection, '#safeguarding-visits-subsection');
        $this->assertSectionDoesExist($safeguardSection, '#safeguarding-visitors-subsection');
        $this->assertSectionDoesExist($safeguardSection, '#safeguarding-furtherinfo-subsection');
        $this->assertSectionContainsText($crawler, '#safeguarding-furtherinfo-subsection', 'nothing else to tell you');
    }

    public function testDontLiveWithClientVisitsEveryday()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getHowOftenDoYouVisit')->andReturn('everyday')
            ->shouldReceive('getHowOftenDoYouPhoneOrVideoCall')->andReturn('everyday')
            ->shouldReceive('getHowOftenDoYouWriteEmailOrLetter')->andReturn('everyday')
            ->shouldReceive('getHowOftenDoesClientSeeOtherPeople')->andReturn('everyday')
            ->shouldReceive('getAnythingElseToTell')->andReturn('nothing else to tell you')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Visits', 'Every day');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Phone and video calls', 'Every day');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Letters and emails', 'Every day');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visitors-subsection', 'How often does the client see other people?', 'Every day');
    }

    public function testDoneLiveWithClientVisitsEveryWeek()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getHowOftenDoYouVisit')->andReturn('once_a_week')
            ->shouldReceive('getHowOftenDoYouPhoneOrVideoCall')->andReturn('once_a_week')
            ->shouldReceive('getHowOftenDoYouWriteEmailOrLetter')->andReturn('once_a_week')
            ->shouldReceive('getHowOftenDoesClientSeeOtherPeople')->andReturn('once_a_week')
            ->shouldReceive('getAnythingElseToTell')->andReturn('nothing else to tell you')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Visits', 'At least once a week');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Phone and video calls', 'At least once a week');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Letters and emails', 'At least once a week');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visitors-subsection', 'How often does the client see other people?', 'At least once a week');
    }

    public function testDoneLiveWithClientVisitsEveryMonth()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getHowOftenDoYouVisit')->andReturn('once_a_month')
            ->shouldReceive('getHowOftenDoYouPhoneOrVideoCall')->andReturn('once_a_month')
            ->shouldReceive('getHowOftenDoYouWriteEmailOrLetter')->andReturn('once_a_month')
            ->shouldReceive('getHowOftenDoesClientSeeOtherPeople')->andReturn('once_a_month')
            ->shouldReceive('getAnythingElseToTell')->andReturn('nothing else to tell you')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Visits', 'At least once a month');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Phone and video calls', 'At least once a month');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Letters and emails', 'At least once a month');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visitors-subsection', 'How often does the client see other people?', 'At least once a month');
    }

    public function testDoneLiveWithClientVisitTwiceAYear()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getHowOftenDoYouVisit')->andReturn('more_than_twice_a_year')
            ->shouldReceive('getHowOftenDoYouPhoneOrVideoCall')->andReturn('more_than_twice_a_year')
            ->shouldReceive('getHowOftenDoYouWriteEmailOrLetter')->andReturn('more_than_twice_a_year')
            ->shouldReceive('getHowOftenDoesClientSeeOtherPeople')->andReturn('more_than_twice_a_year')
            ->shouldReceive('getAnythingElseToTell')->andReturn('nothing else to tell you')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Visits', 'More than twice a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Phone and video calls', 'More than twice a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Letters and emails', 'More than twice a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visitors-subsection', 'How often does the client see other people?', 'More than twice a year');
    }

    public function testDoneLiveWithClientOnceAYear()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getHowOftenDoYouVisit')->andReturn('once_a_year')
            ->shouldReceive('getHowOftenDoYouPhoneOrVideoCall')->andReturn('once_a_year')
            ->shouldReceive('getHowOftenDoYouWriteEmailOrLetter')->andReturn('once_a_year')
            ->shouldReceive('getHowOftenDoesClientSeeOtherPeople')->andReturn('once_a_year')
            ->shouldReceive('getAnythingElseToTell')->andReturn('nothing else to tell you')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Visits', 'Once a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Phone and video calls', 'Once a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Letters and emails', 'Once a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visitors-subsection', 'How often does the client see other people?', 'Once a year');
    }

    public function testDoneLiveWithClientLessThanOnceAYear()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getHowOftenDoYouVisit')->andReturn('less_than_once_a_year')
            ->shouldReceive('getHowOftenDoYouPhoneOrVideoCall')->andReturn('less_than_once_a_year')
            ->shouldReceive('getHowOftenDoYouWriteEmailOrLetter')->andReturn('less_than_once_a_year')
            ->shouldReceive('getHowOftenDoesClientSeeOtherPeople')->andReturn('less_than_once_a_year')
            ->shouldReceive('getAnythingElseToTell')->andReturn('nothing else to tell you')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Visits', 'Less than once a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Phone and video calls', 'Less than once a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visits-subsection', 'Letters and emails', 'Less than once a year');
        $this->assertCheckboxChecked($crawler, '#safeguarding-visitors-subsection', 'How often does the client see other people?', 'Less than once a year');
    }

    public function testShowThatCareIsNotFunded()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('yes')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertSectionContainsText($crawler, '#safeguarding-section', 'Does the client receive care which is paid for?');
        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'Does the client receive care which is paid for?', 'no');
    }

    public function testShowThatCareIsFunded()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('yes')
            ->shouldReceive('getHowIsCareFunded')->andReturn('client_pays_for_all')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertSectionContainsText($crawler, '#safeguarding-section', 'Does the client receive care which is paid for?');
        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'Does the client receive care which is paid for?', 'yes');
    }

    public function testWhenCareIsFundedByClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('yes')
            ->shouldReceive('getHowIsCareFunded')->andReturn('client_pays_for_all')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'How is care funded', 'Client pays for all their own care');
    }

    public function testWhenCareIsFundedByPartiallyByClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('yes')
            ->shouldReceive('getHowIsCareFunded')->andReturn('client_gets_financial_help')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'How is care funded', 'Client gets financial help');
    }

    public function testWhenCareIsFundedBySomeoneElse()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('yes')
            ->shouldReceive('getHowIsCareFunded')->andReturn('all_care_is_paid_by_someone_else')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'How is care funded', 'All care is paid by someone else');
    }

    public function testNoCarePlan()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('yes')
            ->shouldReceive('getHowIsCareFunded')->andReturn('all_care_is_paid_by_someone_else')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);
        $this->assertCheckboxChecked($crawler, '#safeguarding-section', 'There is no care plan');
    }

    public function testCarePlan()
    {
        $reviewDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');

        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->andReturn('no')
            ->shouldReceive('getDoesClientReceivePaidCare')->andReturn('yes')
            ->shouldReceive('getHowIsCareFunded')->andReturn('all_care_is_paid_by_someone_else')
            ->shouldReceive('getWhoIsDoingTheCaring')->andReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->andReturn('yes')
            ->shouldReceive('getWhenWasCarePlanLastReviewed')->andReturn($reviewDate)
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);
        $this->assertCheckboxNotChecked($crawler, '#safeguarding-section', 'There is no care plan');
        $this->assertSectionContainsText($crawler, '#safeguarding-last-review-date', '01 / 2014');
    }

    private function getCrawler($safeguarding)
    {
        $html = $this->twig->render($this->templateName, [
            'safeguarding' => $safeguarding,
        ]);

        $crawler = new Crawler($html);

        return $crawler;
    }
}

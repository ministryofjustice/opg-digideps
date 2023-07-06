<?php

declare(strict_types=1);

use App\Form\FeedbackType;
use Mockery as m;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class FeedbackTypeTest extends TypeTestCase
{
    private $translator;

    public function setUp(): void
    {
        $this->translator = m::mock('Symfony\Contracts\Translation\TranslatorInterface');

        foreach (range(1, 5) as $i => $v) {
            $arg = 'form.satisfactionLevel.choices.%s';
            $this->translator->shouldReceive('trans')->with(sprintf($arg, $v), [], 'feedback')->andReturn('option');
        }

        parent::setUp();
    }

    protected function getExtensions()
    {
        // create a type instance with the mocked dependencies
        $feedbackType = new FeedbackType($this->translator);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$feedbackType], []),
        ];
    }

    /**
     * Test the form submit fails if the honey pot field
     * is filled in.
     */
    public function testSubmitInvalidData()
    {
        $formData = [
            'old_question' => 'some value', // honeypot field
            'specificPage' => 'whole site',
            'comments' => 'Lorem ipsum Iure ad veritatis quidem non.',
        ];

        $form = $this->factory->create(FeedbackType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
    }

    public function tearDown(): void
    {
        m::close();
    }
}

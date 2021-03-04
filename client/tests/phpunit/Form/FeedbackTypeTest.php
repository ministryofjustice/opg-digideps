<?php declare(strict_types=1);

use App\Form\FeedbackType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Mockery as m;

class FeedbackTypeTest extends TypeTestCase
{
    private $translator;

    public function setUp(): void
    {
        $this->translator = m::mock('Symfony\Contracts\Translation\TranslatorInterface');
        $this->translator->shouldReceive('trans')->with(m::any(), [], 'feedback')->andReturnUsing(function ($a) {
            return range(5, 1);
        });

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
     * is filled in
     *
     */
    public function testSubmitInvalidData()
    {
        $formData = [
            'old_question' => 'some value', // honeypot field
            'specificPage' => 'whole site',
            'comments' => 'Lorem ipsum Iure ad veritatis quidem non.',
        ];
        $form = $this->factory->create(FeedbackType::class, $formDataconstraints);

        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
    }

    public function tearDown(): void
    {
        m::close();
    }
}

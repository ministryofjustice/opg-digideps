<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Form;

use OPG\Digideps\Frontend\Form\FeedbackType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedbackTypeTest extends TypeTestCase
{
    private MockObject&TranslatorInterface $translator;

    public function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnCallback(function (string $key, array $parameters, string $domain) {
            $this->assertSame([], $parameters);
            $this->assertSame('feedback', $domain);
            $this->assertStringStartsWith('form.satisfactionLevel.choices.', $key);
            return $key;
        });

        parent::setUp();
    }

    protected function getExtensions(): array
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
    public function testSubmitInvalidData(): void
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
}

<?php declare(strict_types=1);

use AppBundle\Form\FeedbackType;
use Symfony\Component\Form\Test\TypeTestCase;

class FeedbackTypeTest extends TypeTestCase {

    /**
     * Test the form submit fails if the honey pot field
     * is filled in
     */
    public function testSubmitInvalidData()
    {
        $form = $this->factory->create(FeedbackType::class);

        $formData = [
            'old_question' => 'some value', // honeypot field
            'specificPage' => 'whole site',
            'comments' => 'Lorem ipsum Iure ad veritatis quidem non.',
        ];

        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
    }
}

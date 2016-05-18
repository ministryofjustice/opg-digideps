<?php


namespace AppBundle\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

class FormErrorsFormatter
{
    public function toArray(FormInterface $form)
    {
        $ret = [];
        $this->formErrorsToJson($form, $ret);

        return $ret;
    }

    /**
     * @param FormInterface $element
     * @param array         $errors
     * @param string        $name
     */
    private function formErrorsToJson(FormInterface $element, &$errors, $name = '')
    {
        $currentName = $name ? $name.'_'.$element->getName() : $element->getName();

        foreach ($element->getErrors() as $error) { /* @var $error FormError  */
            $errors[$currentName][] = $error->getMessage();
        }
        foreach ($element as $subElement) {
            $this->formErrorsToJson($subElement, $errors, $currentName);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Yes/No radio button to ask the user if they want to add another "thing" (e.g. decision, money transfer).
 *
 * This will typically be invoked via the form_add_another Twig extension, as follows:
 *
 * {{ form_add_another({
 *     'addAnother': form.addAnother,
 *     'translationDomain': 'report-decisions',
 *     'thingTranslationKey': 'form.thing'
 * }) }}
 *
 * where:
 * - addAnother = the name of the AddAnotherThingType form element
 * - translationDomain = domain to translate the "thing" label; e.g. for the decision page, this is 'report-decisions',
 *   and the translations are in the report-decisions.en.yml file (and the cy if we ever build that)
 * - thingTranslationKey = the path inside the translation YAML file (see translationDomain) to the key for the
 *   thing (e.g. "form.thing" references the form.thing translation in the YAML file)
 *
 * Translations for other elements of the yes/no question on the form (heading, label) and error messages are
 * standardised and held in the common.en.yml (label, heading) and validators.en.yml (errors) files. They should
 * consistently be referred to with addAnotherThing as the translation key.
 */
class AddAnotherThingType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => ['Yes' => 'yes', 'No' => 'no'],
            'expanded' => true,

            // this value isn't saved into the entity/db
            'mapped' => false,

            // error message translation is in the validators.*.yml file
            'constraints' => [new NotBlank(['message' => 'addAnotherThing.notBlank', 'groups' => ['add-another']])],

            // translations for label and heading are in common.*.yml file
            'translation_domain' => 'common',

            // this should match the groups given in the constraints setting above
            'validation_groups' => ['add-another'],

            'auto_initialize' => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

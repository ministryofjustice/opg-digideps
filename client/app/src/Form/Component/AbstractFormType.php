<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\GOV\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T of ?object = null
 */
abstract class AbstractFormType extends AbstractType
{
    /**
     * @var array<string, AbstractInput>
     */
    private array $inputs = [];

    abstract protected string $translationDomain {get;}
    abstract protected string $translationPrefix {get;}

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    final public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
    }

    /**
     * @param callable(?string, array<string, string>=): string $t
     * @return array<AbstractInput>
     */
    abstract protected function defineInputs(callable $t): array;

    /**
     * @return array<string, AbstractInput>
     */
    private function getInputs(): array
    {
        if (empty($this->inputs)) {
            $inputs = $this->defineInputs(fn (?string $key, array $parameters = []) => $key === null ? '' : $this->translator->trans("{$this->translationPrefix}.{$key}", $parameters, $this->translationDomain), );
            $namedInputs = [];
            foreach ($inputs as $input) {
                $namedInputs[$input->name] = $input;
            }
            $this->inputs = $namedInputs;
        }
        return $this->inputs;
    }

    final public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->getInputs() as $input) {
            if ($input instanceof AbstractRepeatedInput) {
                $this->buildInput($input->primary, $builder);
                $this->buildInput($input->repeat, $builder);
            } else {
                $this->buildInput($input, $builder);
            }
        }
    }

    private function buildInput(AbstractInput $input, FormBuilderInterface $builder): void
    {
        $options['disabled'] = $input->disabled;
        if ($input->required) {
            $options['required'] = true;
        }
        if ($input->constraints?->notEmpty()) {
            $options['constraints'] = $input->constraints->constraints;
        }
        $builder->add($input->name, $input->symfonyType, $options);
    }

    final public function render(FormView $form): Form
    {
        $renderables = [];
        $errors = [];

        $variables = new ValidatingArray((array)$form->vars);
        $errors[$variables->getStringOrThrow('id')] = AbstractInput::extractErrorMessages($variables);

        $inputs = $this->getInputs();
        /**
        * @var string $childName
        */
        foreach ($form->children as $childName => $childView) {
            $variables = new ValidatingArray((array)$childView->vars);
            $errors[$variables->getStringOrThrow('id')] = AbstractInput::extractErrorMessages($variables);
            if (array_key_exists($childName, $inputs)) {
                $renderables[] = $inputs[$childName]->render($variables);
            } elseif (str_ends_with($childName, '_repeat_')) {
                $primaryName = substr($childName, 0, -8);
                if (array_key_exists($primaryName, $inputs) && $inputs[$primaryName] instanceof AbstractRepeatedInput) {
                    $renderables[] = $inputs[$primaryName]->renderRepeat($variables);
                }
            } else {
                $renderables[] = new HiddenInput($childName)->render($variables);
            }
            $childView->setRendered();
        }
        return new Form($form, $errors, ...$renderables);
    }
}

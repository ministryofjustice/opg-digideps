<?php

namespace AppBundle\Form\Pa;

use AppBundle\Entity\Note as NoteEntity;
use Common\Form\Elements\InputFilters\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Note
 *
 * @package AppBundle\Form\Pa
 */
class Note extends AbstractType
{
    protected $translationDomain = 'report-notes';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Category for the note
     *
     * @var string
     */
    private $category;

    /**
     * Title of the note
     *
     * @var string
     */
    private $title;

    /**
     * Content of the note
     *
     * @var Text|null
     */
    private $content;

    public function __construct(TranslatorInterface $translator, NoteEntity $note)
    {
        /** @var \Translator translator */
        $this->translator = $translator;
        $this->id = $note->getId();
        $this->category = $note->getCategory();
        $this->title = $note->getTitle();
        $this->content = $note->getContent();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add(
                'category',
                ChoiceType::class,
                [
                    'choices' => self::getCategories(),
                    'expanded' => false,
                ]
            )
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->add('save', 'submit');
    }

    /**
     * Set default form options
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['note'],
                'translation_domain' => $this->translationDomain,
                'data-class' => NoteEntity::class
            ]
        );
    }

    /**
     * Return list of translated categories from the Note entity
     *
     * @return array
     */
    private function getCategories()
    {
        $ret = [];


        foreach (NoteEntity::$categories as $categoryId => $cagtegoryTrqnslationKey) {
            $ret[$categoryId] = $this->translate('form.category.entries.' . $cagtegoryTrqnslationKey);
        }

        return $ret;
    }

    /**
     * Get form name
     *
     * @return string
     */
    public function getName()
    {
        return 'note';
    }

    /**
     * Wrapper call to translator
     *
     * @param $key
     * @return string
     */
    private function translate($key)
    {
        return $this->translator->trans($key, [], 'report-note');
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return Text|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param Text|null $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }


}

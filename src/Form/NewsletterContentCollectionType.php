<?php

namespace WebEtDesign\NewsletterBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\NewsletterBundle\Form\Transformer\ContentCollectionTransformer;
use Symfony\Component\Form\AbstractType;

/**
 * Class NewsletterContentDocumentType
 *
 */
class NewsletterContentCollectionType extends AbstractType
{
    private $transformer;

    public function __construct(ContentCollectionTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->transformer->setClass($options['class']);
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
           'class' => null
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'content_collection';
    }

}

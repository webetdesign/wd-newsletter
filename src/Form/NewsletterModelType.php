<?php

namespace WebEtDesign\NewsletterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\NewsletterBundle\Attribute\AbstractModel;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

class NewsletterModelType extends AbstractType
{
    public function __construct(private NewsletterFactory $factory) {}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'placeholder' => 'Choisir',
                'choices' => array_flip(array_map(function (AbstractModel $model){
                    return $model->getName();
                }, $this->factory->getTemplateList())),
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}

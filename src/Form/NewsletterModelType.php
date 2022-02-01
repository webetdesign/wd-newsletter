<?php

namespace WebEtDesign\NewsletterBundle\Form;

use WebEtDesign\NewsletterBundle\Services\ModelProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterModelType extends AbstractType
{
    public function __construct(private ModelProvider $provider) {}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'placeholder' => 'Choisir',
                'choices' => $this->provider->getModelList(),
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getProvider(): ModelProvider
    {
        return $this->provider;
    }
}

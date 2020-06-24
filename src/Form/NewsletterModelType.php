<?php

namespace WebEtDesign\NewsletterBundle\Form;

use WebEtDesign\NewsletterBundle\Services\ModelProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TemplateType
 *
 */
class NewsletterModelType extends AbstractType
{
    private $provider;

    public function __construct(ModelProvider $provider)
    {
        $this->provider = $provider;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'placeholder' => 'Choisir',
                'choices' => $this->provider->getModelList(),
            ]
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @return ModelProvider
     */
    public function getProvider(): ModelProvider
    {
        return $this->provider;
    }
}

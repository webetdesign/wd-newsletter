<?php

namespace WebEtDesign\NewsletterBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\CmsBundle\Factory\BlockFactory;
use WebEtDesign\MediaBundle\Blocks\MediaBlock;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

class AdminNewsletterType extends AbstractType
{

    public function __construct(
        private NewsletterFactory $factory,
        private BlockFactory $blockFactory,
        private array $locales
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['config']) {
            $block = $this->blockFactory->get($options['config']);

            $opts = $block->getFormOptions();
            if (isset($opts['base_block_config']) && $opts['base_block_config']) {
                $opts['base_block_config'] = $options['config'];
            }

            if ($block instanceof MediaBlock){
                $builder->add('media', $block->getFormType(), $opts);
            }else{
                $builder->add('translations', TranslationsType::class, [
                    'label' => false,
                    'locales' => $this->locales,
                    'fields' => [
                        'value' => array_merge($block->getFormOptions(), [
                            'field_type' => $block->getFormType(),
                            'label' => $block->getLabel()
                        ])
                    ],
                    'excluded_fields' => ['code', 'label', 'help'],
                ]);
            }

        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['config']) {
            $block               = $this->blockFactory->get($options['config']);
            $view->vars['block_code'] = $block->getCode();
            $view->vars['block'] = $block;
        }

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
            'block'      => null,
            'config'     => null,
        ]);

    }

    public function getBlockPrefix(): string
    {
        return 'admin_cms_block';
    }


}

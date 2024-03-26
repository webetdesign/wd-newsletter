<?php

namespace WebEtDesign\NewsletterBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use WebEtDesign\CmsBundle\CmsBlock\DynamicBlock;
use WebEtDesign\CmsBundle\Factory\BlockFactory;
use WebEtDesign\CmsBundle\Form\Content\AdminCmsBlockType;
use WebEtDesign\CmsBundle\Registry\BlockRegistryInterface;
use WebEtDesign\MediaBundle\Blocks\MediaBlock;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;

class AdminNewsletterType extends AbstractType
{

    public function __construct(
        private BlockRegistryInterface $blockFactory,
        private array $locales
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['config']) {
            $block = $this->blockFactory->get($options['config']);

            if ($block instanceof MediaBlock) {
                $builder->add('media', $block->getFormType(), $options['config']->getFormOptions());
            } else {
                $builder->add('translations', TranslationsFormsType::class, [
                    'label'        => false,
                    'locales'      => $this->locales,
                    'form_options' => [
                        'data_class' => ContentTranslation::class,
                        'config'     => $options['config']
                    ],
                    'form_type'    => NewsletterContentType::class
                ]);
            }

        }
    }

    public
    function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ) {
        if ($options['config']) {
            $block                    = $this->blockFactory->get($options['config']);
            $view->vars['block_code'] = $block->getCode();
            $view->vars['block']      = $block;
        }

    }


    public
    function configureOptions(
        OptionsResolver $resolver
    ) {
        $resolver->setDefaults([
            'data_class' => Content::class,
            'block'      => null,
            'config'     => null,
        ]);

    }

    public
    function getBlockPrefix(): string
    {
        return 'admin_cms_block';
    }


}

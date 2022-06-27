<?php

namespace WebEtDesign\NewsletterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use WebEtDesign\CmsBundle\Form\Content\AdminCmsBlockType;

class NewsletterContentType extends AbstractType
{
    public function getParent(): string
    {
        return AdminCmsBlockType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('active');
    }


}
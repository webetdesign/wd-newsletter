<?php

namespace WebEtDesign\NewsletterBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class NewsletterUnsubAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('email');
    }


    protected function configureListFields(ListMapper $list)
    {
        unset($this->listModes['mosaic']);

        $list
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de désabonnement',
                'format' => 'd/m/Y'
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('email', TextType::class, [
                'label' => "Email à désabonner",
            ]);
    }

}

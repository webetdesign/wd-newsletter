<?php

namespace WebEtDesign\NewsletterBundle\Admin;

use App\Entity\Group;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Form\NewsletterModelType;
use WebEtDesign\NewsletterBundle\Form\NewsletterContentsType;
use WebEtDesign\NewsletterBundle\Services\EmailService;
use WebEtDesign\NewsletterBundle\Services\RoleProvider;

class NewsletterUnsubAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email');
    }


    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de désabonnement',
                'format' => 'd/m/Y'
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
            $formMapper
                ->add('email', TextType::class, [
                    'label' => "Email à désabonner",
                ])
            ;

    }

}

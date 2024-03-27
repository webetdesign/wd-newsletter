<?php

namespace WebEtDesign\NewsletterBundle\Admin;

use App\Entity\User\Group;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use WebEtDesign\CmsBundle\Form\Content\AdminCmsBlockCollectionType;
use WebEtDesign\CmsBundle\Registry\BlockRegistry;
use WebEtDesign\NewsletterBundle\EventListener\NewsletterContentFormListener;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;
use WebEtDesign\NewsletterBundle\Form\AdminNewsletterType;
use WebEtDesign\NewsletterBundle\Form\NewsletterModelType;

class NewsletterNewsletterAdmin extends AbstractAdmin
{
    protected array $datagridValues = [
        '_page'       => 1,
        '_sort_order' => 'DESC',
        '_sort_by'    => 'id',
    ];

    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly NewsletterFactory $factory,
        protected readonly BlockRegistry $blockRegistry
    ) {
        parent::__construct();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('title');
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('send', 'send/{id}', ['id' => null], ['id' => '\d*']);
        $collection->add('copy', 'copy/{id}', ['id' => null], ['id' => '\d*']);

        parent::configureRoutes($collection);
    }

    protected function configureBatchActions(array $actions): array
    {
        $actions = parent::configureBatchActions($actions);
        unset($actions['delete']);
        return $actions;
    }


    protected function configureListFields(ListMapper $list): void
    {

        $list
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('model', null, [
                'label'    => 'Modèle',
                'template' => '@WDNewsletter/admin/newsletter/model_type.html.twig'
            ])
            ->add('groups', null, [
                'label' => 'Destinataires',
            ]);

        if ($this->canManageContent()) {
            $list->add('isSent', null, [
                'label'    => 'Envoyée',
                'editable' => true
            ]);
        } else {
            $list->add('isSent', null, [
                'label'    => 'Envoyée',
                'editable' => false
            ]);
        }

        $list
            ->add('sentAtFormatted', null, [
                'label' => 'Date d\'envoi',
            ])
            //            ->add('sender', null, [
            //                'label' => "Nom de l'expéditeur",
            //            ])
            //            ->add('email', null, [
            //                'label' => 'Email de retour',
            //            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
                    'copy'   => [
                        'template' => '@WDNewsletter/admin/newsletter/list__action_copy.html.twig'
                    ],
                    'delete' => [
                        'template' => '@WDNewsletter/admin/newsletter/list__action_delete.html.twig'
                    ],
                    'send'   => [
                        'template' => '@WDNewsletter/admin/newsletter/list__action_send.html.twig'
                    ],
                ]
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $this->setFormTheme(array_merge($this->getFormTheme(), [
            "@WebEtDesignCms/admin/form/cms_block.html.twig",
            '@WebEtDesignCms/admin/form/dynamic_block.html.twig',
            '@WebEtDesignCms/admin/form/admin_cms_vars_section.html.twig',
        ]));

        //region Général
        $form
            ->tab('Général', ['box_class' => '']);

        $form
            ->with('', ['box_class' => 'header_none'])
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('model', NewsletterModelType::class, [
                'label' => 'Modèle',
            ])
            ->end()
            ->end();
        //endregion


        if ($this->isCurrentRoute('edit') || $this->getRequest()->isXmlHttpRequest()) {
            //region Envoie
            $form
                ->tab("Options d'envoi", ['box_class' => '']);
            $form
                ->with('', ['box_class' => 'header_none'])
                ->add('groups', EntityType::class, [
                    'label' => "Destinataires",
                    'class' => Group::class,
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true
                ])
                ->add('emailsMore', TextareaType::class, [
                    'label'    => "Liste d'e-mails complémentaires",
                    'required' => false,
                ])
                ->add('sendInAllLocales', CheckboxType::class, [
                    'label'    => "Envoyer dans toutes les langues",
                    'required' => false,
                ]);
            $form
                ->end()
                ->end();
            //endregion
            //region Contenus
            $form
                ->tab('Contenus', ['box_class' => 'header_none', 'class' => 'col-xs-12'])
                ->with('', ['box_class' => 'header_none'])
                ->add('contents', AdminCmsBlockCollectionType::class, [
                    'label'           => false,
                    'templateFactory' => $this->factory,
                    'listener'        => new NewsletterContentFormListener(
                        templateFactory: $this->factory,
                        blockRegistry: $this->blockRegistry,
                        type: AdminNewsletterType::class
                    )
                ])
                ->end();

            //endregion

        }
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('model')
            ->add('title');
    }

    protected function canManageContent(): bool
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $user->hasRole('ROLE_ADMIN_CMS');
    }


}

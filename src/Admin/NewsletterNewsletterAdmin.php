<?php

namespace WebEtDesign\NewsletterBundle\Admin;

use App\Entity\Group;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Form\NewsletterModelType;
use WebEtDesign\NewsletterBundle\Form\NewsletterContentsType;

class NewsletterNewsletterAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page'       => 1,
        '_sort_order' => 'DESC',
        '_sort_by'    => 'id',
    ];

    public function __construct(
        string                        $code,
        string                        $class,
        string                        $baseControllerName,
        private TokenStorageInterface $tokenStorage
    )
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id')
            ->add('title');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('send', 'send/{id}', ['id' => null], ['id' => '\d*']);
        $collection->add('copy', 'copy/{id}', ['id' => null], ['id' => '\d*']);

        parent::configureRoutes($collection);
    }

    protected function configureListFields(ListMapper $list)
    {
        unset($this->listModes['mosaic']);

        $list
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('model', null, [
                'label' => 'Modèle',
                'template' => 'WDNewsletterBundle:admin/newsletter:model_type.html.twig'
            ])
            ->add('groups', null, [
                'label' => 'Destinataires',
            ]);

        if ($this->canManageContent()) {
            $list->add('isSent', null, [
                'label' => 'Envoyée',
                'editable' => true
            ]);
        } else {
            $list->add('isSent', null, [
                'label' => 'Envoyée',
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
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'copy' => [
                        'template' => 'WDNewsletterBundle:admin/newsletter:list__action_copy.html.twig'
                    ],
                    'delete' => [
                        'template' => 'WDNewsletterBundle:admin/newsletter:list__action_delete.html.twig'
                    ],
                    'send' => [
                        'template' => 'WDNewsletterBundle:admin/newsletter:list__action_send.html.twig'
                    ],
                ]
            ]);
    }

    protected function configureFormFields(FormMapper $form)
    {
        /** @var Newsletter $object */
        $object = $this->getSubject();

        $this->setFormTheme(array_merge($this->getFormTheme(), [
            'WDNewsletterBundle:form:newsletter_contents_type.html.twig'
        ]));

        $roleAdmin = $this->canManageContent();

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
            ]);

//        if ($this->isCurrentRoute('edit') || $this->getRequest()->isXmlHttpRequest()) {
//            $form
//                ->add('sender', TextType::class, [
//                    'label' => "Nom de l'expéditeur",
//                ])
//                ->add('email', EmailType::class, [
//                    'label' => 'Email de retour',
//                ]);
//        }
        $form
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
                    'label' => "Liste d'e-mails complémentaires",
                    'required' => false,
                ])
                ->add('sendInAllLocales', CheckboxType::class, [
                    'label' => "Envoyer dans toutes les langues",
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
                ->add('contents', NewsletterContentsType::class, [
                    'label' => false,
                    'by_reference' => false,
                    'role_admin' => $roleAdmin
                ])
                ->end();

            //endregion

        }
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('id')
            ->add('model')
            ->add('title');
    }

    protected function canManageContent(): bool
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user->hasRole('ROLE_ADMIN_CMS');
    }


}

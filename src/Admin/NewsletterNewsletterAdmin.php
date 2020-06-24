<?php

namespace WebEtDesign\NewsletterBundle\Admin;

use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Form\NewsletterModelType;
use WebEtDesign\NewsletterBundle\Form\NewsletterContentsType;
use WebEtDesign\NewsletterBundle\Services\RoleProvider;

class NewsletterNewsletterAdmin extends AbstractAdmin
{
    protected $em;

    protected $datagridValues = [];
    /**
     * @var RoleProvider
     */
    private $provider;

    /**
     * NewsletterNewsletterAdmin constructor.
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param EntityManager $em
     * @param RoleProvider $provider
     */
    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        EntityManager $em,
        RoleProvider $provider
    ) {
        $this->em               = $em;

        parent::__construct($code, $class, $baseControllerName);
        $this->code = $code;
        $this->provider = $provider;
    }


    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('title');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('send', 'send/{id}', ['id' => null], ['id' => '\d*']);

        parent::configureRoutes($collection);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('model', null, [
                'label' => 'Modèle',
            ])
            ->add('sender', null, [
                'label' => "Nom de l'expéditeur",
            ])
            ->add('email', null, [
                'label' => 'Email de retour',
            ])
            ->add('_action', null, [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
                    'send' => [
                        'template' => 'WDNewsletterBundle:admin/newsletter:list__action_send.html.twig'
                    ],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Newsletter $object */
        $object = $this->getSubject();

        $this->setFormTheme(array_merge($this->getFormTheme(), [
            'WDNewsletterBundle:form:newsletter_contents_type.html.twig'
        ]));

        $roleAdmin = $this->canManageContent();

        //region Général
        $formMapper
            ->tab('Général', ['box_class' => '']);

        $formMapper
            ->with('', ['box_class' => 'header_none'])
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('model', NewsletterModelType::class, [
                'label' => 'Modèle',
            ])
            ->add('sender', TextType::class, [
                'label' => "Nom de l'expéditeur",
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email de retour',
            ])
        ;
        $formMapper
            ->end()
            ->end();
        //endregion


        if ($this->isCurrentRoute('edit') || $this->getRequest()->isXmlHttpRequest()) {
            //region Envoie
            $formMapper
                ->tab("Options d'envoi", ['box_class' => '']);

            $formMapper
                ->with('', ['box_class' => 'header_none'])
                ->add('receiver', ChoiceType::class, [
                    'label' => "Destinataires",
                    'required' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $this->provider->getRoleList(),
                ])
                ->add('emailsMore', TextareaType::class, [
                    'label' => "Liste d'e-mails complémentaires",
                    'required' => false,
                ])

            ;
            $formMapper
                ->end()
                ->end();
            //endregion
            //region Contenus
            $formMapper
                ->tab('Contenus', ['box_class' => 'header_none', 'class' => 'col-xs-12'])
                ->with('', ['box_class' => 'header_none'])
                ->add('contents', NewsletterContentsType::class, [
                    'label'        => false,
                    'by_reference' => false,
                    'role_admin' => $roleAdmin
                ])
                ->end();

            //endregion

        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('model')
            ->add('title');
    }

    protected function canManageContent()
    {
        $user = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser();

        return $user->hasRole('ROLE_ADMIN_CMS');
    }

}

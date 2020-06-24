<?php

namespace WebEtDesign\NewsletterBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\Media;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Validator\Constraints\NotBlank;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\FormatterBundle\Form\Type\SimpleFormatterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use WebEtDesign\NewsletterBundle\Services\ModelProvider;

final class NewsletterContentAdmin extends AbstractAdmin
{
    protected $em;
    protected $media_class;
    /**
     * @var ModelProvider
     */
    private $pageProvider;
    /**
     * @var array
     */
    private $locales;

    /**
     * NewsletterContentAdmin constructor.
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param EntityManager $em
     * @param string $media_class
     * @param ModelProvider $pageProvider
     * @param array $locales
     */
    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        EntityManager $em,
        string $media_class,
        ModelProvider $pageProvider,
        array $locales
    ) {
        $this->em             = $em;
        $this->media_class    = $media_class;
        $this->pageProvider   = $pageProvider;

        parent::__construct($code, $class, $baseControllerName);
        $this->locales = $locales;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('type');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->add('id')
            ->add(
                '_action',
                null,
                [
                    'actions' => [
                        'show'   => [],
                        'edit'   => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->getFormBuilder()->setMethod('patch');

        $roleAdmin = $this->canManageContent();
        $admin     = $this;

        /** @var Content $subject */
        $subject = $formMapper->getAdmin()->getSubject();

        $configs = $this->pageProvider->getConfigurationFor($subject->getNewsletter()->getModel());

        $fields = [];

        if ($subject && $subject->getId()) {
            switch ($subject->getType()) {
                case NewsletterContentTypeEnum::TEXT:
                    $fields['value'] = [
                        'label'       => false,
                        'field_type' => TextType::class,
                        'required' => false
                    ];
                    break;
                case NewsletterContentTypeEnum::MEDIA:
                    $formMapper->add(
                        'media',
                        ModelListType::class,
                        [
                            'class'         => Media::class,
                            'required'      => false,
                            'model_manager' => $admin->getModelManager(),
                        ],
                        [
                            "link_parameters" => [
                                'context' => 'newsletter',
                            ],
                        ]
                    );
                    break;

                case NewsletterContentTypeEnum::WYSYWYG:
                    $contents = [];
                    foreach ($configs['contents'] as $content) {
                        $contents[$content['code']] = $content;
                    }
                    $options = $contents[$subject->getCode()]['options'] ?? [];
                    $fields['value'] = [
                        'label'       => false,
                        'field_type' => SimpleFormatterType::class,
                        'format'           => 'richhtml',
                        'ckeditor_context' => $options['ckeditor_context'] ?? 'default',
                        'required'         => false,
                        'auto_initialize'  => false
                     ];
                    break;

                case NewsletterContentTypeEnum::TEXTAREA:
                    $fields['value'] = [
                        'label'       => false,
                        'field_type' => TextareaType::class,
                        'format'           => 'richhtml',
                        'required'        => false,
                        'auto_initialize' => false,
                    ];
                    break;
                case NewsletterContentTypeEnum::COLOR:
                    $fields['value'] = [
                        'label'       => false,
                        'field_type' => ColorType::class,
                        'required'        => false,
                    ];
                    break;
            }

        }
            $formMapper->add('translations', TranslationsType::class, [
                'label'          => false,
                'locales'        => $this->locales,
                'fields'         => $fields,
                'excluded_fields' => ['code', 'label', 'help'],
            ]);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
//        $showMapper
//            ->add('id')
//            ->add('code')
//            ->add('label')
//            ->add('type')
//            ->add('value');
    }

    protected function canManageContent()
    {
        $user = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser();

        return $user->hasRole('ROLE_ADMIN_CMS');
    }


}

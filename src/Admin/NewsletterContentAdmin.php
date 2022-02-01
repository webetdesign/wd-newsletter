<?php

namespace WebEtDesign\NewsletterBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\User;
use Exception;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\NewsletterContentTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\FormatterBundle\Form\Type\SimpleFormatterType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use WebEtDesign\NewsletterBundle\Form\NewsletterContentCollectionType;
use WebEtDesign\NewsletterBundle\Services\ModelProvider;

final class NewsletterContentAdmin extends AbstractAdmin
{
    public function __construct(
        string                        $code,
        string                        $class,
        string                        $baseControllerName,
        private TokenStorageInterface $tokenStorage,
        private string                $media_class,
        private ModelProvider         $modelProvider,
        private array                 $locales,
        private ?string               $document_class,
        private ?string               $actuality_class
    )
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id')
            ->add('type');
    }

    protected function configureListFields(ListMapper $list)
    {
        unset($this->listModes['mosaic']);

        $list
            ->add('id')
            ->add(
                '_action',
                null,
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }

    /**
     * @throws Exception
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->getFormBuilder()->setMethod('patch');

        $admin = $this;

        /** @var Content $subject */
        $subject = $form->getAdmin()->getSubject();

        if (!$subject) return;

        $configs = $this->modelProvider->getConfigurationFor($subject->getNewsletter()->getModel());

        $fields = [];

        $locales = $this->locales;

        if ($subject->getId()) {
            switch ($subject->getType()) {
                case NewsletterContentTypeEnum::TEXT:
                    $fields['value'] = [
                        'label' => false,
                        'field_type' => TextType::class,
                        'required' => false
                    ];
                    break;
                case NewsletterContentTypeEnum::MEDIA:
                    $form->add(
                        'media',
                        ModelListType::class,
                        [
                            'class' => $this->media_class,
                            'required' => false,
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
                    $fields['value'] = [
                        'label' => false,
                        'field_type' => SimpleFormatterType::class,
                        'format' => 'richhtml',
                        'ckeditor_context' => 'newsletter',
                        'required' => false,
                        'auto_initialize' => false,
                    ];
                    break;

                case NewsletterContentTypeEnum::TEXTAREA:
                    $fields['value'] = [
                        'label' => false,
                        'field_type' => TextareaType::class,
                        'format' => 'richhtml',
                        'required' => false,
                        'auto_initialize' => false,
                    ];
                    break;
                case NewsletterContentTypeEnum::COLOR:
                    $fields['value'] = [
                        'label' => false,
                        'field_type' => ColorType::class,
                        'required' => false,
                    ];
                    $locales = ['fr'];
                    break;

                case NewsletterContentTypeEnum::ACTUALITIES:
                case NewsletterContentTypeEnum::DOCUMENTS:
                    $fields['value'] = [
                        'label' => false,
                        'field_type' => NewsletterContentCollectionType::class,
                        'class' => $subject->getType() === NewsletterContentTypeEnum::DOCUMENTS ? $this->document_class : $this->actuality_class,
                        'required' => false,
                        'attr' => [
                            'data-sonata-select2' => 'false',
                            'data-custom-select2' => 'true',
                            'data-class' => $subject->getType() === NewsletterContentTypeEnum::DOCUMENTS ? $this->document_class : $this->actuality_class
                        ]
                    ];
                    break;
            }

        }

        if (isset($fields['value'])) {
            $form->add('translations', TranslationsType::class, [
                'label' => false,
                'locales' => $locales,
                'fields' => $fields,
                'excluded_fields' => ['code', 'label', 'help'],
            ]);
        }
    }

    protected function canManageContent(): bool
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user->hasRole('ROLE_ADMIN_CMS');
    }

}

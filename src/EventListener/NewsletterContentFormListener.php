<?php

namespace WebEtDesign\NewsletterBundle\EventListener;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\FormEvent;
use WebEtDesign\CmsBundle\Entity\CmsContent;
use WebEtDesign\CmsBundle\Registry\BlockRegistry;
use WebEtDesign\CmsBundle\Registry\TemplateRegistry;
use WebEtDesign\MediaBundle\Form\Type\WDMediaType;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

class NewsletterContentFormListener extends ResizeFormListener
{

    public function __construct(
        private readonly NewsletterFactory $templateFactory,
        private readonly BlockRegistry $blockRegistry,
        string $type,
        array $options = []
    ) {
        parent::__construct($type, $options, false, false, true);
    }


    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var CmsContent $data */
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }
        
        if (!\is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // First remove all rows
        foreach ($form as $name => $child) {
            $form->remove($name);
        }


        // Then add all rows again in the correct order
        foreach ($data as $name => $value) {

            if ($value instanceof Content) {
                $template = $value->getNewsletter()->getModel();
                if (isset($template)) {
                    $tpl     = $this->templateFactory->get($template);
                    $config  = $tpl->getBlock($value->getCode());
                    $block   = $config ? $this->blockRegistry->get($config) : null;
                    $options = array_merge($this->options, ['block' => $block, 'config' => $config]);

                    $form->add($name, $this->type, array_replace([
                        'property_path' => '[' . $name . ']',
                    ], $options ?? $this->options));
                }
            }
        }
    }
}
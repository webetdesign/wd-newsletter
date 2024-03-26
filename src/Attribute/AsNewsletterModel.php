<?php

namespace WebEtDesign\NewsletterBundle\Attribute;

use Attribute;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AsNewsletterModel
{
    public function __construct(
        public string $code,
        public string $type = NewsletterFactory::TYPE_NEWSLETTER,
    ) {
    }
}
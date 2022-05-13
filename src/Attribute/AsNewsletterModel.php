<?php

namespace WebEtDesign\NewsletterBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AsNewsletterModel
{
    public function __construct(
        public string $code,
    ) {
    }
}
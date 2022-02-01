<?php

namespace WebEtDesign\NewsletterBundle\Entity;

class NewsletterContentTypeEnum
{
    const TEXT                    = 'TEXT';
    const TEXTAREA                = 'TEXTAREA';
    const WYSYWYG                 = 'WYSYWYG';
    const MEDIA                   = 'MEDIA';
    const COLOR                   = "COLOR";
    const DOCUMENTS               = "DOCUMENTS";
    const ACTUALITIES             = "ACTUALITIES";

    /** @var array user friendly named type */
    protected static array $typeName = [
        self::TEXT                    => 'Text',
        self::TEXTAREA                => 'Textarea',
        self::WYSYWYG                 => 'WYSYWYG',
        self::MEDIA                   => 'Media',
        self::COLOR                   => 'Couleur',
        self::DOCUMENTS               => 'Documents',
        self::ACTUALITIES             => 'Actualités',
    ];

    public static function getName(string $typeShortName): string
    {
        if (!isset(static::$typeName[$typeShortName])) {
            return "Unknown type ($typeShortName)";
        }

        return static::$typeName[$typeShortName];
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TEXT,
            self::TEXTAREA,
            self::WYSYWYG,
            self::MEDIA,
            self::COLOR,
            self::DOCUMENTS,
            self::ACTUALITIES
        ];
    }

    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::getAvailableTypes() as $availableType) {
            $choices[$availableType] = self::getName($availableType);
        }
        return array_flip($choices);
    }
}

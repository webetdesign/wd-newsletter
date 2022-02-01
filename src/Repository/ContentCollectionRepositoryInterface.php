<?php

namespace WebEtDesign\NewsletterBundle\Repository;

interface ContentCollectionRepositoryInterface
{
    public function findForNewsletter(?string $q, int $limit);
}
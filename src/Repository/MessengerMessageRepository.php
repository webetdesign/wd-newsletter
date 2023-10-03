<?php

namespace WebEtDesign\NewsletterBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class MessengerMessageRepository extends ServiceEntityRepository
{

    public function __construct()
    {
        parent::__construct();
    }

}
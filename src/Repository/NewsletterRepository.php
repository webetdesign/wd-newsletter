<?php

namespace WebEtDesign\NewsletterBundle\Repository;

use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Newsletter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Newsletter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Newsletter|null findOneById(int $id)
 * @method Newsletter[]    findAll()
 * @method Newsletter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsletterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newsletter::class);
    }

    public function findAvailableAnalytics (){
        return $this->createQueryBuilder('n')
            ->andWhere('n.isSent = 1')
            ->setMaxResults(10)
            ->orderBy('n.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

}

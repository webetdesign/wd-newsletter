<?php

namespace WebEtDesign\NewsletterBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use WebEtDesign\NewsletterBundle\Entity\NewsletterLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsletterLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsletterLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsletterLog[]    findAll()
 * @method NewsletterLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsletterLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterLog::class);
    }

    public function getAnalytics($site_id, $newsletterId)
    {
        try{
            $total = intval($this->createQueryBuilder('nl')
                ->select('count(nl.id)')
                ->andWhere("nl.newsletterId = :id")
                ->setParameter("id", $newsletterId)
                ->getQuery()
                ->getSingleScalarResult());

            $opened = intval($this->createQueryBuilder('nl')
                ->select('count(nl.id)')
                ->andWhere("nl.newsletterId = :id")
                ->andWhere('(nl.viewed = 1 and nl.clicked = 0)')
                ->setParameter("id", $newsletterId)
                ->getQuery())
                ->getSingleScalarResult();

            $clicked = intval($this->createQueryBuilder('nl')
                ->select('count(nl.id)')
                ->andWhere("nl.newsletterId = :id")
                ->andWhere('(nl.viewed = 1 and nl.clicked = 1)')
                ->setParameter("id", $newsletterId)
                ->getQuery())
                ->getSingleScalarResult();

            if ($total === 0) throw new Exception();

            return [
                'labels' => [
                    ['Non lus', 'fa-2x fa fa-paper-plane'],
                    ['Ouverts', 'fa-2x fa fa-eye'],
                    ['Ouverts et cliqués', 'fa-2x fa fa-mouse-pointer']
                ],
                "values" => [
                    $total - $opened - $clicked,
                    $opened,
                    $clicked
                ],
                'total' => $total,
                'percents' => [
                    (($total - $opened - $clicked) / $total) * 100,
                    (($opened) / $total) * 100,
                    (($clicked) / $total) * 100
                ]
            ];
        }catch (Exception $exception){
            return [
                'labels' => [],
                "values" => [],
                'total' => 0,
                'percent' => []
            ];
        }
    }
}

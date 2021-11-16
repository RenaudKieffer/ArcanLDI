<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }




    /*
    public function findOneBySomeField($value): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findOneBydate($value)
    {
        return $this->createQueryBuilder('t')
            ->join('t.game','g')
            ->andWhere('g.dateEnd > :date')
            ->orWhere('g.dateEnd is NULL')
            ->setParameter('date', $value)
            ->getQuery()
            ->getResult()
            ;
    }
    public function findByGame($value)
    {
        return $this->createQueryBuilder('t')
            ->join('t.game','g')
            ->andWhere('g.id = :name')
            ->setParameter('name', $value)
            ->getQuery()
            ->getResult()
            ;
    }
}

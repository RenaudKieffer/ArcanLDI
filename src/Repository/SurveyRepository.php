<?php

namespace App\Repository;

use App\Entity\Survey;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Survey|null find($id, $lockMode = null, $lockVersion = null)
 * @method Survey|null findOneBy(array $criteria, array $orderBy = null)
 * @method Survey[]    findAll()
 * @method Survey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Survey::class);
    }

     /**
      * @return Survey[] Returns an array of Survey objects
      */

    public function findByNotOnTicket(): array
    {

        return $this->createQueryBuilder('survey')
            ->leftJoin('survey.surveyTickets', 'survey_tickets')
            ->andWhere('survey_tickets.ticket IS NULL')
            ->andWhere('survey.general != 1')
            ->getQuery()
            ->getResult();
    }
    public function findBySurveyByTicket(Ticket $ticket): array
    {

        return $this->createQueryBuilder('survey')
            ->leftJoin('survey.surveyTickets', 'survey_tickets')
            ->andWhere('survey_tickets.ticket = :ticket')
            ->setParameter('ticket', $ticket)
            ->getQuery()
            ->getResult();
    }




    /*
    public function findOneBySomeField($value): ?Survey
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

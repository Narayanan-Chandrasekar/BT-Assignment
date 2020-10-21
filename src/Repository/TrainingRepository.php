<?php

namespace App\Repository;

use App\Entity\Training;
use App\Entity\User;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Training|null find($id, $lockMode = null, $lockVersion = null)
 * @method Training|null findOneBy(array $criteria, array $orderBy = null)
 * @method Training[]    findAll()
 * @method Training[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }

    // /**
    //  * @return Training[] Returns an array of Training objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Training
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByUserId($id, $reservation = false): array
    {
        $entityManager = $this->getEntityManager();

        if(!$reservation)
        {
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Training p
            WHERE p.userId = :id'
            
            )->setParameter('id', $id);
        } else {
            $query = $entityManager->createQuery(
                'SELECT p
                FROM App\Entity\Training p
                WHERE p.userId = :id
                AND p.seatsBooked > 0'
            )->setParameter('id', (integer)$id);
        }    
        // returns an array of Product objects
        return $query->getResult();
    }
    public function findById($id): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Training p
            WHERE p.id = :id'
            
            )->setParameter('id', $id);
            return $query->getResult();
    }  
    
    public function findIfSeatsBooked($id): bool
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Training p
            WHERE p.id = :id
            AND p.seatsBooked > 0'
            
        )->setParameter('id', $id);
          $result =  $query->getResult();
          if($result)
          {
              return true;
          } else {
              return false;
          }
    }
    
    public function findByTopic($id, $q): Array
    {
        $entityManager = $this->getEntityManager();
        if(!empty($id))
        {
            $query = $entityManager->createQuery(
                'SELECT p
                FROM App\Entity\Training p
                WHERE p.userId = :id
                AND p.topic LIKE :q
                AND p.seats > 0'
            )->setParameter('id', $id)
             ->setParameter('q', '%'.$q.'%');
            
        } else {
            $query = $entityManager->createQuery(
                'SELECT p
                FROM App\Entity\Training p
                WHERE p.topic LIKE :q
                AND p.seats > 0'
                
            )->setParameter('q', '%'.$q.'%');
        }
        
         return  $query->getResult();
          
    }

    public function findBookedTrainings($userId): Array
    {
        $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery(
                'SELECT p
                FROM App\Entity\Training p WHERE EXISTS ( SELECT IDENTITY(q.trainingId) FROM App\Entity\Reservation q
                WHERE p.id = q.trainingId
                AND q.userId = :id
                AND q.active = 1
                ) '

            )->setParameter('id', $userId);
             
        
         return  $query->getResult();
          
    }

    public function findUpcomingReservations($userId): Array
    {
        $entityManager = $this->getEntityManager();
            $query = $entityManager->createQuery(
                'SELECT p
                FROM App\Entity\Training p WHERE EXISTS ( SELECT IDENTITY(q.trainingId) FROM App\Entity\Reservation q
                WHERE p.id = q.trainingId
                AND q.userId = :id
                AND q.active = 1)
                AND p.start >  CURRENT_DATE()
                
                '
            )->setParameter('id', $userId);
             
        
         return  $query->getResult();
          
    }

    
}

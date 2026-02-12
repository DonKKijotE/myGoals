<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

/**
 * Obtiene todos los eventos que se solapan con una semana determinada.
 *
 * Lógica de filtrado:
 * - e.start >= :weekStart AND e.endTime <= :weekEnd
 *      → Solo eventos que empiezan y terminan completamente dentro de la semana.
 * - e.start <= :weekEnd AND e.endTime >= :weekStart
 *      → Todos los eventos que tengan algún día dentro de la semana, incluso si empiezan antes o terminan después.
 *
 * Se usa la segunda opción para incluir eventos de varios días.
 *
 * @param \DateTimeInterface $weekStart Inicio de la semana
 * @param \DateTimeInterface $weekEnd   Fin de la semana
 * @return Event[] Lista de eventos de la semana
 */

    public function findEventsForWeek(
        \DateTimeInterface $startOfWeek,
        \DateTimeInterface $endOfWeek
    ): array {
        return $this->createQueryBuilder('e')
            ->where('e.start <= :weekEnd')
            ->andWhere('e.endtime >= :weekStart')
            ->setParameter('weekStart', $startOfWeek)
            ->setParameter('weekEnd', $endOfWeek)
            ->orderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Función para sacar los eventos de un periodo definido.
    * @param \DateTimeInterface $periodStart Inicio del periodo
    * @param \DateTimeInterface $periodEnd   Fin del periodo
    * @return Event[] Lista de eventos del periodo
    */

       public function findEventsForPeriod(
           \DateTimeInterface $startOfPeriod,
           \DateTimeInterface $endOfPeriod
       ): array {
           return $this->createQueryBuilder('e')
               ->where('e.start <= :periodEnd')
               ->andWhere('e.endtime >= :periodStart')
               ->setParameter('periodStart', $startOfPeriod)
               ->setParameter('periodEnd', $endOfPeriod)
               ->orderBy('e.start', 'ASC')
               ->getQuery()
               ->getResult();
       }
}

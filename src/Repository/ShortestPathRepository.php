<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\ShortestPath;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShortestPath|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShortestPath|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShortestPath[]    findAll()
 * @method ShortestPath[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShortestPathRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShortestPath::class);
    }
}

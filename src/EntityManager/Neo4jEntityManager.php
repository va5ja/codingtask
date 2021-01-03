<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Edge;
use App\Entity\Node;

class Neo4jEntityManager extends AbstractEntityManager implements EntityManagerInterface
{
    protected $supportedEntities = [Node::class, Edge::class];
}

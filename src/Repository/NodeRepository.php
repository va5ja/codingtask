<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Node;
use App\EntityManager\Neo4jEntityManager;
use Laudis\Neo4j\Client;
use Symfony\Component\Uid\UuidV4;

class NodeRepository extends Neo4jRepository implements RepositoryInterface
{
    /** @var Neo4jEntityManager */
    protected $entityManager;

    /** @var Client */
    protected $client;

    public function __construct(Neo4jEntityManager $entityManager, Client $client)
    {
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function find($id): ?object
    {
        $data = $this->createEntitiesFromResults($this->client->run('MATCH (n:Node {uuid: $id}) RETURN n', $id));

        return $data[0] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function findAll(): array
    {
        return $this->createEntitiesFromResults($this->client->run('MATCH (n:Node) RETURN n'));
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $wherePart = $this->getQueryPart(self::PART_WHERE, 'n', $criteria);
        $results = $this->client->run("MATCH (n:Node) $wherePart RETURN n", $criteria);

        return $this->createEntitiesFromResults($results);
    }

    /**
     * @inheritdoc
     */
    public function findOneBy(array $criteria): ?object
    {
        $data = $this->findBy($criteria);

        return $data[0] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function count(array $criteria): int
    {
        $wherePart = $this->getQueryPart(self::PART_WHERE, 'n', $criteria);

        $result = $this->client->run("MATCH (n:Node) $wherePart RETURN COUNT(n) AS count", $criteria);

        return (int)$result->first()->get('count');
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return Node::class;
    }

    public function getOneByGraphAndQuery(string $graphId, string $query): ?Node
    {
        $results = $this->client->run(
            'MATCH (n:Node) WHERE n.graphId = $graphId AND (n.uuid = $query OR n.name = $query) RETURN n',
            ['graphId' => $graphId, 'query' => $query]
        );

        $data = $this->createEntitiesFromResults($results);

        return $data[0] ?? null;
    }

    public function getShortestPath(string $fromNodeId, string $toNodeId): array
    {
        $results = $this->client->run(
            'MATCH (a:Node {uuid: $fromNodeId}), (b:Node {uuid: $toNodeId}), p = shortestPath((a)-[*]-(b))
            WHERE length(p) > 1
            RETURN p',
            ['fromNodeId' => $fromNodeId, 'toNodeId' => $toNodeId],
            'backup'
        );

        if ($results->count()) {
            return $results->first()->get('p');
        }

        return [];
    }

    public function createNode(Node $node): void
    {
        $results = $this->client->run(
            'CREATE (n:Node {uuid: apoc.create.uuid(), graphId: $graphId, name: $name}) RETURN n',
            ['graphId' => (string)$node->getGraphId(), 'name' => $node->getName()]
        );

        if ($results->count()) {
            $node->setId(UuidV4::fromString($results->first()->get('n')['uuid']));
        }
    }

    public function updateNode(Node $node): void
    {
        $this->client->run(
            "MATCH (n:Node {uuid: \$uuid}) SET n.name = \$name RETURN n",
            ['uuid' => (string)$node->getId(), 'name' => $node->getName()]
        );
    }

    public function deleteNode(Node $node): void
    {
        $this->client->run(
            'MATCH (n:Node {uuid: $uuid, graphId: $graphId}) DETACH DELETE n',
            ['uuid' => (string)$node->getId(), 'graphId' => (string)$node->getGraphId()]
        );
    }

    protected function createEntityFromResult($result): Node
    {
        $properties = $result->get('n');

        return (new Node())
            ->setId(UuidV4::fromString($properties['uuid']))
            ->setGraphId(UuidV4::fromString($properties['graphId']))
            ->setName($properties['name']);
    }
}

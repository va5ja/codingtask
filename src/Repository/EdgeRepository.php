<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Edge;
use App\Entity\Node;
use Symfony\Component\Uid\UuidV4;

class EdgeRepository extends Neo4jRepository implements RepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function find($id): ?object
    {
        $data = $this->createEntitiesFromResults($this->client->run(
            'MATCH ()-[e:Edge {uuid: $id}]->() RETURN startNode(e) AS from, endNode(e) AS to, e', $id
        ));

        return $data[0] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function findAll(): array
    {
        return $this->createEntitiesFromResults($this->client->run(
            'MATCH ()-[e:Edge]->() RETURN startNode(e) AS from, endNode(e) AS to, e'
        ));
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $wherePart = $this->getQueryPart(self::PART_WHERE, 'e', $criteria);
        $results = $this->client->run(
            "MATCH (a:Node)-[e:Edge]->(b:Node) 
            $wherePart AND a.graphId = \$graphId AND b.graphId = \$graphId
            RETURN startNode(e) AS from, endNode(e) AS to, e",
            $criteria
        );

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
        $wherePart = $this->getQueryPart(self::PART_WHERE, 'e', $criteria);

        $result = $this->client->run("MATCH ()-[e:Edge]->() $wherePart RETURN COUNT(e) AS count", $criteria);

        return (int)$result->first()->get('count');
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return Edge::class;
    }

    public function createEdge(Edge $edge): void
    {
        $fromNode = $edge->getFromNode();
        $toNode = $edge->getToNode();

        $fromNodeProperties = implode(', ', array_keys(array_filter([
            'uuid: $auuid' => (string)$fromNode->getId(),
            'graphId: $graphId' => (string)$edge->getGraphId(),
            'name: $aname' => $fromNode->getName()
        ])));
        $toNodeProperties = implode(', ', array_keys(array_filter([
            'uuid: $buuid' => (string)$toNode->getId(),
            'graphId: $graphId' => (string)$edge->getGraphId(),
            'name: $bname' => $toNode->getName()
        ])));

        $results = $this->client->run(
            "MERGE (a:Node {{$fromNodeProperties}})
            ON CREATE SET a.uuid = apoc.create.uuid()
            MERGE (b:Node {{$toNodeProperties}})
            ON CREATE SET b.uuid = apoc.create.uuid()
            CREATE (a)-[e:Edge {uuid: apoc.create.uuid(), graphId: \$graphId}]->(b) 
            RETURN startNode(e) AS from, endNode(e) AS to, e",
            [
                'graphId' => (string)$edge->getGraphId(),
                'auuid' => (string)$fromNode->getId(),
                'aname' => $fromNode->getName(),
                'buuid' => (string)$toNode->getId(),
                'bname' => $toNode->getName()
            ]
        );

        if ($results->count()) {
            $from = $results->first()->get('from');
            $to = $results->first()->get('to');
            $edge->setId(UuidV4::fromString($results->first()->get('e')['uuid']));
            $edge->getFromNode()->setId(UuidV4::fromString($from['uuid']));
            $edge->getFromNode()->setName($from['name']);
            $edge->getFromNode()->setGraphId(UuidV4::fromString($from['graphId']));
            $edge->getToNode()->setId(UuidV4::fromString($to['uuid']));
            $edge->getToNode()->setName($to['name']);
            $edge->getToNode()->setGraphId(UuidV4::fromString($to['graphId']));
        }
    }

    public function updateEdge(Edge $edge): void
    {
        $fromNode = $edge->getFromNode();
        $toNode = $edge->getToNode();

        $fromNodeMatch = implode(', ', array_keys(array_filter([
            'uuid: $auuid' => (string)$fromNode->getId(),
            'graphId: $graphId' => (string)$edge->getGraphId(),
            'name: $aname' => $fromNode->getName()
        ])));
        $toNodeMatch = implode(', ', array_keys(array_filter([
            'uuid: $buuid' => (string)$toNode->getId(),
            'graphId: $graphId' => (string)$edge->getGraphId(),
            'name: $bname' => $toNode->getName()
        ])));

        $results = $this->client->run(
            "MATCH ()-[e:Edge {uuid: \$euuid}]->() 
            MATCH (a:Node {{$fromNodeMatch}})
            CALL apoc.refactor.from(e, a)
            YIELD input, output
            RETURN input, output, a AS from",
            [
                'euuid' => (string)$edge->getId(),
                'graphId' => (string)$edge->getGraphId(),
                'auuid' => (string)$fromNode->getId(),
                'aname' => $fromNode->getName(),
            ]
        );

        $from = $results->first()->get('from');
        $fromNode
            ->setId(UuidV4::fromString($from['uuid']))
            ->setGraphId(UuidV4::fromString($from['graphId']))
            ->setName($from['name']);

        $results = $this->client->run(
            "MATCH ()-[e:Edge {uuid: \$euuid}]->() 
            MATCH (b:Node {{$toNodeMatch}})
            CALL apoc.refactor.to(e, b)
            YIELD input, output
            RETURN input, output, b AS to",
            [
                'euuid' => (string)$edge->getId(),
                'graphId' => (string)$edge->getGraphId(),
                'buuid' => (string)$toNode->getId(),
                'bname' => $toNode->getName()
            ]
        );

        $to = $results->first()->get('to');
        $toNode
            ->setId(UuidV4::fromString($to['uuid']))
            ->setGraphId(UuidV4::fromString($to['graphId']))
            ->setName($to['name']);
    }

    public function deleteEdge(Edge $edge): void
    {
        $this->client->run(
            'MATCH ()-[e:Edge {uuid: $uuid, graphId: $graphId}]->() DELETE e',
            ['uuid' => (string)$edge->getId(), 'graphId' => (string)$edge->getGraphId()]
        );
    }

    protected function createEntityFromResult($result): Edge
    {
        $fromNode = $result->get('from');
        $toNode = $result->get('to');
        $properties = $result->get('e');

        return (new Edge())
            ->setId(UuidV4::fromString($properties['uuid']))
            ->setGraphId(UuidV4::fromString($properties['graphId']))
            ->setFromNode((new Node())
                ->setId(UuidV4::fromString($fromNode['uuid']))
                ->setGraphId(UuidV4::fromString($fromNode['graphId']))
                ->setName($fromNode['name']))
            ->setToNode((new Node())
                ->setId(UuidV4::fromString($toNode['uuid']))
                ->setGraphId(UuidV4::fromString($toNode['graphId']))
                ->setName($toNode['name']));
    }
}

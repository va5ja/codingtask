<?php declare(strict_types=1);

namespace App\EntityManager;

use App\Entity\Edge;
use App\Entity\Node;
use App\EntityManager\Metadata\MetadataProvider;
use App\Exception\InvalidArgumentException;
use App\Repository\RepositoryInterface;
use Laudis\Neo4j\Client;
use Psr\Log\LoggerInterface;

class Neo4jEntityManager extends AbstractEntityManager implements EntityManagerInterface
{
    protected $supportedEntities = [Node::class, Edge::class];

    /** @var Client */
    protected $client;

    public function __construct(LoggerInterface $logger, MetadataProvider $metadataProvider, Client $client)
    {
        parent::__construct($logger, $metadataProvider);

        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function getRepository(string $className): RepositoryInterface
    {
        $repoClass = 'App\\Repository\\' . (new \ReflectionClass($className))->getShortName() . self::REPOSITORY_SUFFIX;

        if (!class_exists($repoClass)) {
            throw new InvalidArgumentException("Repository \"$repoClass\" for entity \"$className\" does not exist.");
        }

        return new $repoClass($this, $this->client);
    }
}

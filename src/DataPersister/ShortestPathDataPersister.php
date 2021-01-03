<?php declare(strict_types=1);

namespace App\DataPersister;

use App\Entity\Graph;
use App\Entity\Node;
use App\Entity\ShortestPath;
use App\EntityManager\EntityManagerProvider;
use App\Message\FindShortestPath;
use App\Request\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;

class ShortestPathDataPersister extends AbstractRepositoryPersister
{
    /** @var MessageBusInterface */
    private $bus;

    public function __construct(
        EntityManagerProvider $entityManagerProvider,
        Security $security,
        LoggerInterface $logger,
        MessageBusInterface $bus
    ) {
        parent::__construct($entityManagerProvider, $security, $logger);

        $this->bus = $bus;
    }

    /**
     * @inheritdoc
     */
    public function persistData(array $data, Request $request): array
    {
        $entityManager = $this->entityManagerProvider->getManagerForClass($request->getEntityClassName());
        $nodeRepository = $this->entityManagerProvider->getManagerForClass(Node::class)->getRepository(Node::class);
        /** @var Graph|null $graph */
        $graph = $this->entityManagerProvider
            ->getManagerForClass(Graph::class)
            ->find(Graph::class, $request->getIdentifiers()[Graph::class] ?? []);
        $graphId = $graph ? (string)$graph->getId() : null;

        if ($request->getMethod() === Request::METHOD_POST) {
            /** @var ShortestPath $entity */
            foreach ($data as $entity) {
                $fromNode = $nodeRepository->getOneByGraphAndQuery($graphId, $entity->getFromNode());
                $toNode = $nodeRepository->getOneByGraphAndQuery($graphId, $entity->getToNode());

                $entity->setGraph($graph);
                $entity->setFromNode($fromNode ? (string)$fromNode->getId() : null);
                $entity->setToNode($toNode ? (string)$toNode->getId() : null);
                $entityManager->persist($entity);
            }
        }

        $entityManager->flush();

        foreach ($data as $entity) {
            $this->bus->dispatch(new FindShortestPath((string)$entity->getId()));
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(array $data, Request $request): bool
    {
        return $request->getEntityClassName() === ShortestPath::class;
    }
}

<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Node;
use App\Entity\ShortestPath;
use App\EntityManager\EntityManagerProvider;
use App\Message\FindShortestPath;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FindShortestPathHandler implements MessageHandlerInterface
{
    /** @var ParameterBagInterface */
    private $params;

    /** @var EntityManagerProvider */
    private $entityManagerProvider;

    public function __construct(ParameterBagInterface $params, EntityManagerProvider $entityManagerProvider)
    {
        $this->params = $params;
        $this->entityManagerProvider = $entityManagerProvider;
    }

    public function __invoke(FindShortestPath $message)
    {
        $shortestPathId = $message->getShortestPathId();
        $entityManager = $this->entityManagerProvider->getManagerForClass(ShortestPath::class);
        /** @var ShortestPath $shortestPath */
        $shortestPath = $entityManager->find(ShortestPath::class, $shortestPathId);
        $nodeRepository = $this->entityManagerProvider
            ->getManagerForClass(Node::class)
            ->getRepository(Node::class);

        $shortestPath->setStatus(ShortestPath::STATUS_PROCESSING);
        $entityManager->flush();

        $pathList = $nodeRepository->getShortestPath($shortestPath->getFromNode(), $shortestPath->getToNode());

        $fp = fopen($this->params->get('kernel.project_dir') . "/public/paths/$shortestPathId.csv", 'w');
        fputcsv($fp, ['Step', 'Node', 'Edge', 'Node']);

        $step = [];
        foreach ($pathList as $index => $item) {
            $step[] = array_key_exists('name', $item) ? '(' . $item['name'] . ') ' . $item['uuid'] : $item['uuid'];

            if ($index % 2 === 0 && $index !== 0) {
                fputcsv($fp, array_merge([$index / 2], $step));
                $step = [$step[2]];
            }
        }
        fclose($fp);

        $shortestPath->setDataFile("/paths/$shortestPathId.csv");
        $shortestPath->setStatus(ShortestPath::STATUS_DONE);
        $entityManager->flush();
    }
}

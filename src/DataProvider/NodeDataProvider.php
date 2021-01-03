<?php declare(strict_types=1);

namespace App\DataProvider;

use App\Entity\Graph;
use App\Entity\Node;
use App\Request\Request;

class NodeDataProvider extends AbstractRepositoryProvider
{
    public function provideData(Request $request)
    {
        $entityIdentifiers = $request->getEntityIdentifiers();
        $graphIdentifiers = $request->getIdentifiers()[Graph::class] ?? [];
        $entityRepository = $this->getEntityRepository($request);

        return $request->getDataProviderType() === Request::PROVIDER_COLLECTION ?
            $entityRepository->findBy(['graphId' => $graphIdentifiers['id']]) :
            $entityRepository->findOneBy([
                'uuid' => $entityIdentifiers['id'],
                'graphId' => $graphIdentifiers['id']
            ]);
    }

    public function isApplicable(Request $request): bool
    {
        return $request->getEntityClassName() === Node::class;
    }
}

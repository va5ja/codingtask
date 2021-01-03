<?php declare(strict_types=1);

namespace App\DataProvider;

use App\Request\Request;

class SimpleItemDataProvider extends AbstractRepositoryProvider
{
    /**
     * @inheritdoc
     */
    public function provideData(Request $request)
    {
        $identifiers = $request->getEntityIdentifiers();
        $entityRepository = $this->getEntityRepository($request);

        return $identifiers ? $entityRepository->find($identifiers) : $entityRepository->findAll();
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(Request $request): bool
    {
        return true;
    }

    public static function getDefaultPriority(): int
    {
        return -1;
    }
}

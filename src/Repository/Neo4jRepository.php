<?php declare(strict_types=1);

namespace App\Repository;

abstract class Neo4jRepository
{
    protected const PART_WHERE = 'WHERE';
    protected const PART_SET = 'SET';

    protected function getQueryPart(string $type, string $prefix, array $criteria): string
    {
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $conditions[] = "$prefix.$key = \$$key";
        }

        $separator = $type === self::PART_SET ? ',' : 'AND';

        return count($conditions) ? $type . ' ' . implode(" $separator ", $conditions) : '';
    }

    protected function createEntitiesFromResults(iterable $results): array
    {
        $data = [];
        foreach ($results as $result) {
            $data[] = $this->createEntityFromResult($result);
        }

        return $data;
    }
}

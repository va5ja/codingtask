<?php declare(strict_types=1);

namespace App\Message;

class FindShortestPath
{
    private $shortestPathId;

    public function __construct(string $shortestPathId)
    {
        $this->shortestPathId = $shortestPathId;
    }

    public function getShortestPathId(): string
    {
        return $this->shortestPathId;
    }
}

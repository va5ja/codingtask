<?php declare(strict_types=1);

namespace App\Entity;

use App\Annotation\Uuid;
use Symfony\Component\Uid\AbstractUid;

class Edge
{
    /**
     * @Uuid(version=4, encode="base32")
     */
    private $id;

    /**
     * @Uuid(version=4, encode="base32")
     */
    private $graphId;

    private $fromNode;

    private $toNode;

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public function setId(AbstractUid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getGraphId(): ?AbstractUid
    {
        return $this->graphId;
    }

    public function setGraphId(AbstractUid $graphId): self
    {
        $this->graphId = $graphId;

        return $this;
    }

    public function getFromNode(): ?Node
    {
        return $this->fromNode;
    }

    public function setFromNode(Node $fromNode): self
    {
        $this->fromNode = $fromNode;

        return $this;
    }

    public function getToNode(): ?Node
    {
        return $this->toNode;
    }

    public function setToNode(Node $toNode): self
    {
        $this->toNode = $toNode;

        return $this;
    }
}

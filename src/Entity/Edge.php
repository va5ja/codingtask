<?php declare(strict_types=1);

namespace App\Entity;

use App\Annotation\Id;
use Symfony\Component\Uid\AbstractUid;

class Edge extends AbstractObservableEntity
{
    /**
     * @Id(type="uuid", version=4, encode="base32")
     */
    private $id;

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

        $this->notify(self::EVENT_PROPERTY_CHANGE, ['property' => 'id']);

        return $this;
    }

    public function getGraphId(): ?AbstractUid
    {
        return $this->graphId;
    }

    public function setGraphId(AbstractUid $graphId): self
    {
        $this->graphId = $graphId;

        $this->notify(self::EVENT_PROPERTY_CHANGE, ['property' => 'graphId']);

        return $this;
    }

    public function getFromNode(): ?Node
    {
        return $this->fromNode;
    }

    public function setFromNode(Node $fromNode): self
    {
        $this->fromNode = $fromNode;

        $this->notify(self::EVENT_PROPERTY_CHANGE, ['property' => 'fromNode']);

        return $this;
    }

    public function getToNode(): ?Node
    {
        return $this->toNode;
    }

    public function setToNode(Node $toNode): self
    {
        $this->toNode = $toNode;

        $this->notify(self::EVENT_PROPERTY_CHANGE, ['property' => 'toNode']);

        return $this;
    }
}

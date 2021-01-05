<?php declare(strict_types=1);

namespace App\Entity;

use App\Annotation\Id;
use Symfony\Component\Uid\AbstractUid;

class Node extends AbstractObservableEntity
{
    /**
     * @Id(type="uuid", version=4, encode="base32")
     */
    private $id;

    private $graphId;

    private $name;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        $this->notify(self::EVENT_PROPERTY_CHANGE, ['property' => 'name']);

        return $this;
    }
}

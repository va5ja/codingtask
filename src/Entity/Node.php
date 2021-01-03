<?php declare(strict_types=1);

namespace App\Entity;

use App\Annotation\Uuid;
use Symfony\Component\Uid\AbstractUid;

class Node
{
    /**
     * @Uuid(version=4, encode="base32")
     */
    private $id;

    /**
     * @Uuid(version=4, encode="base32")
     */
    private $graphId;

    private $name;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}

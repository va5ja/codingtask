<?php declare(strict_types=1);

namespace App\Entity;

use App\Annotation\Id;
use App\Exception\InvalidArgumentException;
use App\Repository\ShortestPathRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass=ShortestPathRepository::class)
 * @ORM\Table(name="`shortest_path`")
 */
class ShortestPath
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE = 'done';

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     * @Id(type="uuid", version=4, encode="base32")
     */
    private $id;

    /**
     * @var Graph
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Graph")
     * @ORM\JoinColumn(name="graph_id", referencedColumnName="id", nullable=false)
     */
    private $graph;

    /**
     * @ORM\Column(name="from_node_id", type="string", length=36, nullable=false)
     * @Assert\NotBlank
     */
    private $fromNode;

    /**
     * @ORM\Column(name="to_node_id", type="string", length=36, nullable=false)
     * @Assert\NotBlank
     */
    private $toNode;

    /**
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     * @Assert\NotBlank
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="data_file", type="string", length=255, nullable=true)
     */
    private $dataFile;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public function setId(AbstractUid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getGraph(): ?Graph
    {
        return $this->graph;
    }

    public function setGraph(Graph $graph): self
    {
        $this->graph = $graph;

        return $this;
    }

    public function getFromNode(): ?string
    {
        return $this->fromNode;
    }

    public function setFromNode(string $fromNode): self
    {
        $this->fromNode = $fromNode;

        return $this;
    }

    public function getToNode(): ?string
    {
        return $this->toNode;
    }

    public function setToNode(string $toNode): self
    {
        $this->toNode = $toNode;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_DONE
        ])) {
            throw new InvalidArgumentException('Invalid status');
        }

        $this->status = $status;

        return $this;
    }

    public function getDataFile(): ?string
    {
        return $this->dataFile;
    }

    public function setDataFile(?string $dataFile): self
    {
        $this->dataFile = $dataFile;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = $this->createdAt ?: new \DateTime('now');
        $this->updatedAt = $this->updatedAt ?: new \DateTime('now');
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }
}

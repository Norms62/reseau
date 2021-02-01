<?php

namespace App\Entity;

use App\Repository\TicketsRegroupRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Om;
use App\Entity\Masao;

/**
 * @ORM\Entity(repositoryClass=TicketsRegroupRepository::class)
 */
class TicketsRegroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\Column(type="integer")
     */
    private $om_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $masao_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getMasaoId(): ?Masao
    {
        return $this->masao_id;
    }

    public function setMasaoId(?int $masao_id): self
    {
        $this->masao_id = $masao_id;

        return $this;
    }

    public function getOmId(): ?om
    {
        return $this->om_id;
    }

    public function setOmId(?int $om_id): self
    {
        $this->om_id = $om_id;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }
}

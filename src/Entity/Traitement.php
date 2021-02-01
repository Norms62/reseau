<?php

namespace App\Entity;

use App\Repository\TraitementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TraitementRepository::class)
 */
class Traitement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nbTicketRegroup;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ref;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date_soumission;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mise_a_jour;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rapporteur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resume;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $temps;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $priorite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $impact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etat;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resolution;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $categorie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $action;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\Column(type="integer")
     */
    private $ticket_om;

    /**
     * @ORM\Column(type="integer")
     */
    private $ticket_masao;

    /**
     * @ORM\Column(type="integer")
     */
    private $ticket_regroup;

    public function _contruct($type,$nbTicket,$ref,$date_s,$date_m,$date_c,$rapporteur,$resume,$description,$temps,$priorite,$impact,$etat,$resolution,$categorie){
        $this->type = $type;
        $this->nbTicketRegroup = $nbTicket;
        $this->ref = $ref;
        $this->date_soumission = $date_s;
        $this->mise_a_jour = $date_m;
        $this->date_creation = $date_c;
        $this->rapporteur = $rapporteur;
        $this->resume = $resume;
        $this->description = $description;
        $this->temps = $temps;
        $this->priorite = $priorite;
        $this->impact = $impact;
        $this->etat = $etat;
        $this->resolution = $resolution;
        $this->categorie = $categorie;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNbTicketRegroup(): ?string
    {
        return $this->nbTicketRegroup;
    }

    public function setNbTicketRegroup(?string $nbTikcketRegroup): self
    {
        $this->nbTicketRegroup = $nbTikcketRegroup;

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

    public function getDateSoumission(): ?string
    {
        return $this->date_soumission;
    }

    public function setDateSoumission(string $date_soumission): self
    {
        $this->date_soumission = $date_soumission;

        return $this;
    }

    public function getMiseAJour(): ?string
    {
        return $this->mise_a_jour;
    }

    public function setMiseAJour(string $mise_a_jour): self
    {
        $this->mise_a_jour = $mise_a_jour;

        return $this;
    }

    public function getRapporteur(): ?string
    {
        return $this->rapporteur;
    }

    public function setRapporteur(?string $rapporteur): self
    {
        $this->rapporteur = $rapporteur;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTemps(): ?string
    {
        return $this->temps;
    }

    public function setTemps(?string $temps): self
    {
        $this->temps = $temps;

        return $this;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): self
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getImpact(): ?string
    {
        return $this->impact;
    }

    public function setImpact(?string $impact): self
    {
        $this->impact = $impact;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getResolution(): ?string
    {
        return $this->resolution;
    }

    public function setResolution(?string $resolution): self
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
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

    public function getTicketOm(): ?int
    {
        return $this->ticket_om;
    }

    public function setTicketOm(?int $ticket_om): self
    {
        $this->ticket_om = $ticket_om;

        return $this;
    }

    public function getTicketMasao(): ?int
    {
        return $this->ticket_masao;
    }

    public function setTicketMasao(?int $ticket_masao): self
    {
        $this->ticket_masao = $ticket_masao;

        return $this;
    }

    public function getTicketRegroup(): ?int
    {
        return $this->ticket_regroup;
    }

    public function setTicketRegroup(?int $ticket_regroup): self
    {
        $this->ticket_regroup = $ticket_regroup;

        return $this;
    }
}

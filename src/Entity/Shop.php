<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopRepository::class)
 */
class Shop
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $id_parent;

    /**
     * @ORM\Column(type="integer")
     */
    private $artikelNr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $config;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $bezeichnungKurz;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $bezeichnungLang;

    /**
     * @ORM\Column(type="integer")
     */
    private $lager;

    /**
     * @ORM\Column(type="float")
     */
    private $preis;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verkauf;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $einkaufspreis;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lieferant;

    /**
     * @ORM\Column(type="integer")
     */
    private $geschlecht;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reorder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdParent(): ?int
    {
        return $this->id_parent;
    }

    public function setIdParent(?int $id_parent): self
    {
        $this->id_parent = $id_parent;

        return $this;
    }

    public function getArtikelNr(): ?int
    {
        return $this->artikelNr;
    }

    public function setArtikelNr(int $artikelNr): self
    {
        $this->artikelNr = $artikelNr;

        return $this;
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(?string $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getBezeichnungKurz(): ?string
    {
        return $this->bezeichnungKurz;
    }

    public function setBezeichnungKurz(string $bezeichnungKurz): self
    {
        $this->bezeichnungKurz = $bezeichnungKurz;

        return $this;
    }

    public function getBezeichnungLang(): ?string
    {
        return $this->bezeichnungLang;
    }

    public function setBezeichnungLang(?string $bezeichnungLang): self
    {
        $this->bezeichnungLang = $bezeichnungLang;

        return $this;
    }

    public function getLager(): ?int
    {
        return $this->lager;
    }

    public function setLager(int $lager): self
    {
        $this->lager = $lager;

        return $this;
    }

    public function getPreis(): ?float
    {
        return $this->preis;
    }

    public function setPreis(float $preis): self
    {
        $this->preis = $preis;

        return $this;
    }

    public function getVerkauf(): ?bool
    {
        return $this->verkauf;
    }

    public function setVerkauf(bool $verkauf): self
    {
        $this->verkauf = $verkauf;

        return $this;
    }

    public function getEinkaufspreis(): ?float
    {
        return $this->einkaufspreis;
    }

    public function setEinkaufspreis(float $einkaufspreis): self
    {
        $this->einkaufspreis = $einkaufspreis;

        return $this;
    }

    public function getLieferant(): ?string
    {
        return $this->lieferant;
    }

    public function setLieferant(?string $lieferant): self
    {
        $this->lieferant = $lieferant;

        return $this;
    }

    public function getGeschlecht(): ?int
    {
        return $this->geschlecht;
    }

    public function setGeschlecht(int $geschlecht): self
    {
        $this->geschlecht = $geschlecht;

        return $this;
    }

    public function getReorder(): ?bool
    {
        return $this->reorder;
    }

    public function setReorder(bool $reorder): self
    {
        $this->reorder = $reorder;

        return $this;
    }
}

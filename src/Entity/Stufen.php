<?php

namespace App\Entity;

use App\Repository\StufenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StufenRepository::class)
 */
class Stufen
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
    private $stufenname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $jahrgaenge;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStufenname(): ?string
    {
        return $this->stufenname;
    }

    public function setStufenname(string $stufenname): self
    {
        $this->stufenname = $stufenname;

        return $this;
    }

    public function getJahrgaenge(): ?string
    {
        return $this->jahrgaenge;
    }

    public function setJahrgaenge(string $jahrgaenge): self
    {
        $this->jahrgaenge = $jahrgaenge;

        return $this;
    }
}

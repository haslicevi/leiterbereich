<?php

namespace App\Entity;

use App\Repository\MitgliederRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MitgliederRepository::class)
 */
class Mitglieder
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
    private $nachname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $vorname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ceviname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $strasse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nr;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $plz;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ort;

    /**
     * @ORM\Column(type="smallint")
     */
    private $geschlecht;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $geburtstag;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telefon;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $handy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mail;

    /**
     * @ORM\Column(type="smallint")
     */
    private $funktion;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $allergie;

    /**
     * @ORM\Column(type="string", length=5000, nullable=true)
     */
    private $bemerkung;

    /**
     * @ORM\Column(type="smallint")
     */
    private $krawatte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bankkonto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $kurs;

    /**
     * @ORM\Column(type="smallint")
     */
    private $stufe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jsNummer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ahvNummer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(string $nachname): self
    {
        $this->nachname = $nachname;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(string $vorname): self
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getCeviname(): ?string
    {
        return $this->ceviname;
    }

    public function setCeviname(?string $ceviname): self
    {
        $this->ceviname = $ceviname;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(?string $strasse): self
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getNr(): ?string
    {
        return $this->nr;
    }

    public function setNr(?string $nr): self
    {
        $this->nr = $nr;

        return $this;
    }

    public function getPlz(): ?int
    {
        return $this->plz;
    }

    public function setPlz(?int $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(?string $ort): self
    {
        $this->ort = $ort;

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

    public function getGeburtstag(): ?\DateTimeInterface
    {
        return $this->geburtstag;
    }

    public function setGeburtstag(?\DateTimeInterface $geburtstag): self
    {
        $this->geburtstag = $geburtstag;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): self
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function getHandy(): ?string
    {
        return $this->handy;
    }

    public function setHandy(?string $handy): self
    {
        $this->handy = $handy;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getFunktion(): ?int
    {
        return $this->funktion;
    }

    public function setFunktion(int $funktion): self
    {
        $this->funktion = $funktion;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAllergie(): ?string
    {
        return $this->allergie;
    }

    public function setAllergie(?string $allergie): self
    {
        $this->allergie = $allergie;

        return $this;
    }

    public function getBemerkung(): ?string
    {
        return $this->bemerkung;
    }

    public function setBemerkung(?string $bemerkung): self
    {
        $this->bemerkung = $bemerkung;

        return $this;
    }

    public function getKrawatte(): ?int
    {
        return $this->krawatte;
    }

    public function setKrawatte(int $krawatte): self
    {
        $this->krawatte = $krawatte;

        return $this;
    }

    public function getBankkonto(): ?string
    {
        return $this->bankkonto;
    }

    public function setBankkonto(?string $bankkonto): self
    {
        $this->bankkonto = $bankkonto;

        return $this;
    }

    public function getKurs(): ?string
    {
        return $this->kurs;
    }

    public function setKurs(?string $kurs): self
    {
        $this->kurs = $kurs;

        return $this;
    }

    public function getStufe(): ?int
    {
        return $this->stufe;
    }

    public function setStufe(int $stufe): self
    {
        $this->stufe = $stufe;

        return $this;
    }

    public function getJsNummer(): ?string
    {
        return $this->jsNummer;
    }

    public function setJsNummer(?string $jsNummer): self
    {
        $this->jsNummer = $jsNummer;

        return $this;
    }

    public function getAhvNummer(): ?string
    {
        return $this->ahvNummer;
    }

    public function setAhvNummer(?string $ahvNummer): self
    {
        $this->ahvNummer = $ahvNummer;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}

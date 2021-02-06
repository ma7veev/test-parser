<?php

namespace App\Entity;

use App\Repository\ParsingResultRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParsingResultRepository::class)
 */
class ParsingResult
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
    private $date_parsed;

    /**
     * @ORM\Column(type="integer")
     */
    private $count_new;

    /**
     * @ORM\Column(type="integer")
     */
    private $count_upd;

    /**
     * @ORM\Column(type="integer")
     */
    private $pointer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateParsed(): ?\DateTimeInterface
    {
        return $this->date_parsed;
    }

    public function setDateParsed(\DateTimeInterface $date_parsed): self
    {
        $this->date_parsed = $date_parsed;

        return $this;
    }

    public function getCountNew(): ?int
    {
        return $this->count_new;
    }

    public function setCountNew(int $count_new): self
    {
        $this->count_new = $count_new;

        return $this;
    }

    public function getCountUpd(): ?int
    {
        return $this->count_upd;
    }

    public function setCountUpd(int $count_upd): self
    {
        $this->count_upd = $count_upd;

        return $this;
    }

    public function getPointer(): ?int
    {
        return $this->pointer;
    }

    public function setPointer(int $pointer): self
    {
        $this->pointer = $pointer;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birtdate = null;

    #[ORM\Column]
    private ?int $number = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBirtdate(): ?\DateTimeInterface
    {
        return $this->birtdate;
    }

    public function setBirtdate(\DateTimeInterface $birtdate): self
    {
        $this->birtdate = $birtdate;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }
}

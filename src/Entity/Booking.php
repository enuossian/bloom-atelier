<?php

namespace App\Entity;

use App\Enum\BookingStatus;
use App\Repository\BookingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(enumType: BookingStatus::class)]
    private ?BookingStatus $status = BookingStatus::Pending;

    #[ORM\Column]
    private ?bool $confirmationSent = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    /**
     * @var Collection<int, BookItem>
     */
    #[ORM\OneToMany(targetEntity: BookItem::class, mappedBy: 'booking', orphanRemoval: true)]
    private Collection $bookItems;

    public function __construct()
    {
        $this->bookItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function calculateTotalAmount(): string
    {
        $total = 0;

        foreach ($this->bookItems as $bookItem) {
            $total += $bookItem->getPrice();
        }

        return $total;
    }

    public function getStatus(): ?BookingStatus
    {
        return $this->status;
    }

    public function setStatus(BookingStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isConfirmationSent(): ?bool
    {
        return $this->confirmationSent;
    }

    public function setConfirmationSent(bool $confirmationSent): static
    {
        $this->confirmationSent = $confirmationSent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, BookItem>
     */
    public function getBookItems(): Collection
    {
        return $this->bookItems;
    }

    public function addBookItem(BookItem $bookItem): static
    {
        if (!$this->bookItems->contains($bookItem)) {
            $this->bookItems->add($bookItem);
            $bookItem->setBooking($this);
        }

        return $this;
    }

    public function removeBookItem(BookItem $bookItem): static
    {
        if ($this->bookItems->removeElement($bookItem)) {
            // set the owning side to null (unless already changed)
            if ($bookItem->getBooking() === $this) {
                $bookItem->setBooking(null);
            }
        }

        return $this;
    }
}

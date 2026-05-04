<?php

namespace App\Entity;

use App\Enum\BookingStatus;
use App\Enum\SessionStatus;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull(message: 'Le service est obligatoire.')]
    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $reference = null;

    #[Assert\NotNull(message: 'La date de début est obligatoire.')]
    #[Assert\GreaterThan('now', message: 'La date de début doit être dans le futur.')]
    #[ORM\Column]
    private ?\DateTimeImmutable $startTime = null;

    #[Assert\NotNull(message: 'La date de fin est obligatoire.')]
    #[Assert\GreaterThan(propertyPath: 'startTime', message: 'La date de fin doit être postérieure à la date de début.')]
    #[ORM\Column]
    private ?\DateTimeImmutable $endTime = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'Le lieu doit contenir au maximum {{ limit }} caractères.',
    )]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[Assert\NotBlank(message: 'Le nombre de participants est obligatoire.')]
    #[Assert\Range(
        min: 1,
        max: 3,
        notInRangeMessage: 'Le nombre de participants doit être compris entre 1 et 3.',
    )]
    #[ORM\Column]
    private ?int $maxParticipants = null;

    #[ORM\Column(enumType: SessionStatus::class)]
    private ?SessionStatus $status = SessionStatus::Available;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, BookItem>
     */
    #[ORM\OneToMany(targetEntity: BookItem::class, mappedBy: 'session')]
    private Collection $bookItems;

    public function __construct()
    {
        $this->bookItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
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

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(?int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;

        return $this;
    }

    public function getStatus(): ?SessionStatus
    {
        return $this->status;
    }

    public function setStatus(SessionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

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

    // Validation personnalisée pour vérifier que la durée de la session correspond à la durée du service
    #[Assert\Callback]
    public function validateDuration(ExecutionContextInterface $context): void
    {
        // arrête le script si l'une des valeurs est nulle
        if (null === $this->startTime || null === $this->endTime || null === $this->service) {
            return;
        }

        // récupère la différence entre le début et la fin de la session
        // diff() méthode native de DateTimeImmutable retourne un objet DateInterval
        $diff = $this->startTime->diff($this->endTime);

        // récupère h de différence et multiplie par 60 pour convertir en minutes
        // récupère les minutes de différence
        $actualMinutes = ($diff->h * 60) + $diff->i;

        // récupère la durée du service pour comparaison
        $maxMinutes = $this->service->getDuration();

        if ($actualMinutes !== $maxMinutes) {
            // context est injecté par symfony permet de créet et attacher des erreurs
            // buildViolation prépare le message d'erreur
            $context->buildViolation('La durée de la session doit être exactement de {{ max }}.')
                ->setParameter('{{ max }}', $this->service->getDisplayDuration())
                // indique que l'erreu doit s'afficher sur le champ endTime
                ->atPath('endTime')
                // appel permettant de déclencher l'erreur
                ->addViolation();
        }
    }

    // Compte le nombre de bookings payés pour cette session
    public function getPaidCount(): int
    {
        $paidCount = 0;
        foreach ($this->bookItems as $bookItem) {
            if (BookingStatus::Paid === $bookItem->getBooking()->getStatus()) {
                ++$paidCount;
            }
        }

        return $paidCount;
    }

    // Met à jour le statut de la session en fonction de la date et du nombre de places restantes
    public function updateStatus(): void
    {
        // Session passée
        if ($this->endTime < new \DateTimeImmutable()) {
            $this->status = SessionStatus::Completed;

            return;
        }

        // Compter les places prises (bookings payés) et mettre à jour le statut selon les places restantes
        if ($this->getPaidCount() >= $this->maxParticipants) {
            $this->status = SessionStatus::Full;
        } else {
            $this->status = SessionStatus::Available;
        }
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
            $bookItem->setSession($this);
        }

        return $this;
    }

    public function removeBookItem(BookItem $bookItem): static
    {
        if ($this->bookItems->removeElement($bookItem)) {
            // set the owning side to null (unless already changed)
            if ($bookItem->getSession() === $this) {
                $bookItem->setSession(null);
            }
        }

        return $this;
    }
}

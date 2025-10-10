<?php

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
class Episode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $airDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $season = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $episodeNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    /**
     * @var Collection<int, Character>
     */
    #[ORM\ManyToMany(targetEntity: Character::class, inversedBy: 'episodes')]
    private Collection $characters {
        get {
            return $this->characters;
        }
    }

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'episode')]
    private Collection $reviews {
        get {
            return $this->reviews;
        }
    }

    public function __construct()
    {
        $this->characters = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAirDate(): \DateTimeInterface
    {
        return $this->airDate;
    }

    public function setAirDate(?\DateTime $airDate): static
    {
        $this->airDate = $airDate;

        return $this;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(?string $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getEpisodeNumber(): ?string
    {
        return $this->episodeNumber;
    }

    public function setEpisodeNumber(?string $episodeNumber): static
    {
        $this->episodeNumber = $episodeNumber;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function addCharacter(Character $character): static
    {
        if (!$this->characters->contains($character)) {
            $this->characters->add($character);
        }

        return $this;
    }

    public function removeCharacter(Character $character): static
    {
        $this->characters->removeElement($character);

        return $this;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        $this->reviews->removeElement($review);

        return $this;
    }

    public function getCharacters(): ArrayCollection|Collection
    {
        return $this->characters;
    }
}

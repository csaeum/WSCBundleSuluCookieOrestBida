<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'WSC\SuluCookieConsentBundle\Repository\CookieItemRepository')]
#[ORM\Table(name: 'wsc_cookie_item')]
class CookieItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cookie::class, inversedBy: 'cookieItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Cookie $cookie = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $lifetime = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    /**
     * @var Collection<int, CookieItemTranslation>
     */
    #[ORM\OneToMany(
        mappedBy: 'cookieItem',
        targetEntity: CookieItemTranslation::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'locale'
    )]
    private Collection $translations;

    private ?string $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCookie(): ?Cookie
    {
        return $this->cookie;
    }

    public function setCookie(?Cookie $cookie): self
    {
        $this->cookie = $cookie;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLifetime(): ?string
    {
        return $this->lifetime;
    }

    public function setLifetime(?string $lifetime): self
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return Collection<int, CookieItemTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getTranslation(?string $locale = null): ?CookieItemTranslation
    {
        $locale = $locale ?? $this->locale;
        if (!$locale) {
            return null;
        }

        if ($this->translations->containsKey($locale)) {
            return $this->translations->get($locale);
        }

        return null;
    }

    protected function createTranslation(string $locale): CookieItemTranslation
    {
        $translation = new CookieItemTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

    public function getDescription(): ?string
    {
        return $this->getTranslation()?->getDescription();
    }

    public function setDescription(?string $description): self
    {
        $translation = $this->getTranslation() ?? $this->createTranslation($this->locale);
        $translation->setDescription($description);
        return $this;
    }
}

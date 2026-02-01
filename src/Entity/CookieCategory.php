<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableTrait;

#[ORM\Entity(repositoryClass: 'WSC\SuluCookieConsentBundle\Repository\CookieCategoryRepository')]
#[ORM\Table(name: 'wsc_cookie_category')]
class CookieCategory
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'cookie_categories';
    public const LIST_KEY = 'cookie_categories';
    public const FORM_KEY = 'cookie_category_details';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $technicalName;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = false;

    #[ORM\Column(type: 'boolean')]
    private bool $readOnly = false;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    /**
     * @var Collection<int, CookieCategoryTranslation>
     */
    #[ORM\OneToMany(
        mappedBy: 'cookieCategory',
        targetEntity: CookieCategoryTranslation::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'locale'
    )]
    private Collection $translations;

    /**
     * @var Collection<int, Cookie>
     */
    #[ORM\OneToMany(
        mappedBy: 'category',
        targetEntity: Cookie::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $cookies;

    private ?string $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->cookies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }

    public function setTechnicalName(string $technicalName): self
    {
        $this->technicalName = $technicalName;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;
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
     * @return Collection<int, CookieCategoryTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getTranslation(?string $locale = null): ?CookieCategoryTranslation
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

    protected function createTranslation(string $locale): CookieCategoryTranslation
    {
        $translation = new CookieCategoryTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

    public function getName(): ?string
    {
        return $this->getTranslation()?->getName();
    }

    public function setName(?string $name): self
    {
        $translation = $this->getTranslation() ?? $this->createTranslation($this->locale);
        $translation->setName($name);
        return $this;
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

    /**
     * @return Collection<int, Cookie>
     */
    public function getCookies(): Collection
    {
        return $this->cookies;
    }

    public function addCookie(Cookie $cookie): self
    {
        if (!$this->cookies->contains($cookie)) {
            $this->cookies->add($cookie);
            $cookie->setCategory($this);
        }

        return $this;
    }

    public function removeCookie(Cookie $cookie): self
    {
        if ($this->cookies->removeElement($cookie)) {
            if ($cookie->getCategory() === $this) {
                $cookie->setCategory(null);
            }
        }

        return $this;
    }
}

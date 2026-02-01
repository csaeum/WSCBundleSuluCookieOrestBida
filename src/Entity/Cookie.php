<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableTrait;

#[ORM\Entity(repositoryClass: 'WSC\SuluCookieConsentBundle\Repository\CookieRepository')]
#[ORM\Table(name: 'wsc_cookie')]
class Cookie
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'cookies';
    public const LIST_KEY = 'cookies';
    public const FORM_KEY = 'cookie_details';

    public const LEGAL_BASIS_CONSENT = 'consent';
    public const LEGAL_BASIS_LEGITIMATE_INTEREST = 'legitimate_interest';
    public const LEGAL_BASIS_CONTRACT = 'contract';
    public const LEGAL_BASIS_LEGAL_OBLIGATION = 'legal_obligation';

    public const PROCESSING_LOCATION_EU = 'eu';
    public const PROCESSING_LOCATION_USA = 'usa';
    public const PROCESSING_LOCATION_WORLDWIDE = 'worldwide';
    public const PROCESSING_LOCATION_GERMANY = 'germany';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CookieCategory::class, inversedBy: 'cookies')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CookieCategory $category = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $technicalName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $scriptUrl = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $legalBasis = self::LEGAL_BASIS_CONSENT;

    #[ORM\Column(type: 'string', length: 50)]
    private string $processingLocation = self::PROCESSING_LOCATION_EU;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    /**
     * @var Collection<int, CookieTranslation>
     */
    #[ORM\OneToMany(
        mappedBy: 'cookie',
        targetEntity: CookieTranslation::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'locale'
    )]
    private Collection $translations;

    /**
     * @var Collection<int, CookieItem>
     */
    #[ORM\OneToMany(
        mappedBy: 'cookie',
        targetEntity: CookieItem::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $cookieItems;

    private ?string $locale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->cookieItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?CookieCategory
    {
        return $this->category;
    }

    public function setCategory(?CookieCategory $category): self
    {
        $this->category = $category;
        return $this;
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

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function getScriptUrl(): ?string
    {
        return $this->scriptUrl;
    }

    public function setScriptUrl(?string $scriptUrl): self
    {
        $this->scriptUrl = $scriptUrl;
        return $this;
    }

    public function getLegalBasis(): string
    {
        return $this->legalBasis;
    }

    public function setLegalBasis(string $legalBasis): self
    {
        $this->legalBasis = $legalBasis;
        return $this;
    }

    public function getProcessingLocation(): string
    {
        return $this->processingLocation;
    }

    public function setProcessingLocation(string $processingLocation): self
    {
        $this->processingLocation = $processingLocation;
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
     * @return Collection<int, CookieTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getTranslation(?string $locale = null): ?CookieTranslation
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

    protected function createTranslation(string $locale): CookieTranslation
    {
        $translation = new CookieTranslation($this, $locale);
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

    public function getPrivacyPolicyUrl(): ?string
    {
        return $this->getTranslation()?->getPrivacyPolicyUrl();
    }

    public function setPrivacyPolicyUrl(?string $privacyPolicyUrl): self
    {
        $translation = $this->getTranslation() ?? $this->createTranslation($this->locale);
        $translation->setPrivacyPolicyUrl($privacyPolicyUrl);
        return $this;
    }

    public function getDataCollected(): ?string
    {
        return $this->getTranslation()?->getDataCollected();
    }

    public function setDataCollected(?string $dataCollected): self
    {
        $translation = $this->getTranslation() ?? $this->createTranslation($this->locale);
        $translation->setDataCollected($dataCollected);
        return $this;
    }

    public function getDataPurpose(): ?string
    {
        return $this->getTranslation()?->getDataPurpose();
    }

    public function setDataPurpose(?string $dataPurpose): self
    {
        $translation = $this->getTranslation() ?? $this->createTranslation($this->locale);
        $translation->setDataPurpose($dataPurpose);
        return $this;
    }

    /**
     * @return Collection<int, CookieItem>
     */
    public function getCookieItems(): Collection
    {
        return $this->cookieItems;
    }

    public function addCookieItem(CookieItem $cookieItem): self
    {
        if (!$this->cookieItems->contains($cookieItem)) {
            $this->cookieItems->add($cookieItem);
            $cookieItem->setCookie($this);
        }

        return $this;
    }

    public function removeCookieItem(CookieItem $cookieItem): self
    {
        if ($this->cookieItems->removeElement($cookieItem)) {
            if ($cookieItem->getCookie() === $this) {
                $cookieItem->setCookie(null);
            }
        }

        return $this;
    }
}

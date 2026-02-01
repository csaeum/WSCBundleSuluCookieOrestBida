<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'wsc_cookie_translation')]
class CookieTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cookie::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Cookie $cookie;

    #[ORM\Column(type: 'string', length: 10)]
    private string $locale;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $privacyPolicyUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $dataCollected = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $dataPurpose = null;

    public function __construct(Cookie $cookie, string $locale)
    {
        $this->cookie = $cookie;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCookie(): Cookie
    {
        return $this->cookie;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrivacyPolicyUrl(): ?string
    {
        return $this->privacyPolicyUrl;
    }

    public function setPrivacyPolicyUrl(?string $privacyPolicyUrl): self
    {
        $this->privacyPolicyUrl = $privacyPolicyUrl;
        return $this;
    }

    public function getDataCollected(): ?string
    {
        return $this->dataCollected;
    }

    public function setDataCollected(?string $dataCollected): self
    {
        $this->dataCollected = $dataCollected;
        return $this;
    }

    public function getDataPurpose(): ?string
    {
        return $this->dataPurpose;
    }

    public function setDataPurpose(?string $dataPurpose): self
    {
        $this->dataPurpose = $dataPurpose;
        return $this;
    }
}

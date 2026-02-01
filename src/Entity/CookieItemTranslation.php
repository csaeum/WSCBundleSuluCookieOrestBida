<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'wsc_cookie_item_translation')]
class CookieItemTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CookieItem::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CookieItem $cookieItem;

    #[ORM\Column(type: 'string', length: 10)]
    private string $locale;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct(CookieItem $cookieItem, string $locale)
    {
        $this->cookieItem = $cookieItem;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCookieItem(): CookieItem
    {
        return $this->cookieItem;
    }

    public function getLocale(): string
    {
        return $this->locale;
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
}

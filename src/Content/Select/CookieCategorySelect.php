<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Content\Select;

use WSC\SuluCookieConsentBundle\Repository\CookieCategoryRepository;

class CookieCategorySelect
{
    public function __construct(
        private CookieCategoryRepository $categoryRepository
    ) {
    }

    /**
     * @return array<int, array{name: int|string, title: string}>
     */
    public function getValues(string $locale): array
    {
        $categories = $this->categoryRepository->findAllForLocale($locale);

        $values = [];
        foreach ($categories as $category) {
            $category->setLocale($locale);
            $values[] = [
                'name' => $category->getId(),
                'title' => $category->getName() ?? $category->getTechnicalName(),
            ];
        }

        return $values;
    }

    public function getDefaultValue(): ?int
    {
        $categories = $this->categoryRepository->findAll();

        // Return first category ID as default, or null if none exist
        if (count($categories) > 0) {
            return $categories[0]->getId();
        }

        return null;
    }
}

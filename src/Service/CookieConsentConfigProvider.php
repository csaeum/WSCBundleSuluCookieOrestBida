<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Service;

use WSC\SuluCookieConsentBundle\Entity\Cookie;
use WSC\SuluCookieConsentBundle\Entity\CookieCategory;
use WSC\SuluCookieConsentBundle\Repository\CookieCategoryRepository;
use WSC\SuluCookieConsentBundle\Repository\CookieRepository;

class CookieConsentConfigProvider
{
    public function __construct(
        private CookieCategoryRepository $categoryRepository,
        private CookieRepository $cookieRepository,
        private array $config
    ) {
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getFullConfig(string $locale): array
    {
        $categories = $this->categoryRepository->findAllActive($locale);
        $cookies = $this->cookieRepository->findAllActive($locale);

        $categoriesData = [];
        foreach ($categories as $category) {
            $categoriesData[] = $this->serializeCategory($category, $locale);
        }

        $cookiesData = [];
        foreach ($cookies as $cookie) {
            $cookiesData[] = $this->serializeCookie($cookie, $locale);
        }

        $revision = $this->generateRevision($categoriesData, $cookiesData);

        return [
            'config' => $this->config,
            'categories' => $categoriesData,
            'cookies' => $cookiesData,
            'revision' => $revision,
        ];
    }

    private function serializeCategory(CookieCategory $category, string $locale): array
    {
        $category->setLocale($locale);

        return [
            'id' => $category->getId(),
            'technicalName' => $category->getTechnicalName(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
            'enabled' => $category->isEnabled(),
            'readOnly' => $category->isReadOnly(),
            'position' => $category->getPosition(),
        ];
    }

    private function serializeCookie(Cookie $cookie, string $locale): array
    {
        $cookie->setLocale($locale);

        $cookieItems = [];
        foreach ($cookie->getCookieItems() as $item) {
            if (!$item->isActive()) {
                continue;
            }
            $item->setLocale($locale);
            $cookieItems[] = [
                'name' => $item->getName(),
                'lifetime' => $item->getLifetime(),
                'description' => $item->getDescription(),
            ];
        }

        return [
            'id' => $cookie->getId(),
            'categoryId' => $cookie->getCategory()?->getId(),
            'categoryTechnicalName' => $cookie->getCategory()?->getTechnicalName(),
            'technicalName' => $cookie->getTechnicalName(),
            'name' => $cookie->getName(),
            'description' => $cookie->getDescription(),
            'provider' => $cookie->getProvider(),
            'scriptUrl' => $cookie->getScriptUrl(),
            'legalBasis' => $cookie->getLegalBasis(),
            'processingLocation' => $cookie->getProcessingLocation(),
            'privacyPolicyUrl' => $cookie->getPrivacyPolicyUrl(),
            'dataCollected' => $cookie->getDataCollected(),
            'dataPurpose' => $cookie->getDataPurpose(),
            'cookieItems' => $cookieItems,
        ];
    }

    private function generateRevision(array $categories, array $cookies): string
    {
        $data = json_encode(['categories' => $categories, 'cookies' => $cookies]);
        return substr(md5($data), 0, 8);
    }
}

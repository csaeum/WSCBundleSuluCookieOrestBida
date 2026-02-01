<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WSC\SuluCookieConsentBundle\Entity\Cookie;
use WSC\SuluCookieConsentBundle\Entity\CookieCategory;
use WSC\SuluCookieConsentBundle\Entity\CookieItem;
use WSC\SuluCookieConsentBundle\Repository\CookieCategoryRepository;
use WSC\SuluCookieConsentBundle\Repository\CookieItemRepository;
use WSC\SuluCookieConsentBundle\Repository\CookieRepository;

class ImportExportController extends AbstractController
{
    public function __construct(
        private CookieCategoryRepository $categoryRepository,
        private CookieRepository $cookieRepository,
        private CookieItemRepository $cookieItemRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function exportAction(): Response
    {
        $categories = $this->categoryRepository->findAll();
        $cookies = $this->cookieRepository->findAll();

        $exportData = [
            'version' => '1.0.0',
            'exportDate' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'categories' => [],
            'cookies' => [],
        ];

        foreach ($categories as $category) {
            $categoryData = [
                'technicalName' => $category->getTechnicalName(),
                'enabled' => $category->isEnabled(),
                'readOnly' => $category->isReadOnly(),
                'position' => $category->getPosition(),
                'active' => $category->isActive(),
                'translations' => [],
            ];

            foreach ($category->getTranslations() as $translation) {
                $categoryData['translations'][$translation->getLocale()] = [
                    'name' => $translation->getName(),
                    'description' => $translation->getDescription(),
                ];
            }

            $exportData['categories'][] = $categoryData;
        }

        foreach ($cookies as $cookie) {
            $cookieData = [
                'technicalName' => $cookie->getTechnicalName(),
                'categoryTechnicalName' => $cookie->getCategory()?->getTechnicalName(),
                'provider' => $cookie->getProvider(),
                'scriptUrl' => $cookie->getScriptUrl(),
                'legalBasis' => $cookie->getLegalBasis(),
                'processingLocation' => $cookie->getProcessingLocation(),
                'position' => $cookie->getPosition(),
                'active' => $cookie->isActive(),
                'translations' => [],
                'cookieItems' => [],
            ];

            foreach ($cookie->getTranslations() as $translation) {
                $cookieData['translations'][$translation->getLocale()] = [
                    'name' => $translation->getName(),
                    'description' => $translation->getDescription(),
                    'privacyPolicyUrl' => $translation->getPrivacyPolicyUrl(),
                    'dataCollected' => $translation->getDataCollected(),
                    'dataPurpose' => $translation->getDataPurpose(),
                ];
            }

            foreach ($cookie->getCookieItems() as $item) {
                $itemData = [
                    'name' => $item->getName(),
                    'lifetime' => $item->getLifetime(),
                    'position' => $item->getPosition(),
                    'active' => $item->isActive(),
                    'translations' => [],
                ];

                foreach ($item->getTranslations() as $translation) {
                    $itemData['translations'][$translation->getLocale()] = [
                        'description' => $translation->getDescription(),
                    ];
                }

                $cookieData['cookieItems'][] = $itemData;
            }

            $exportData['cookies'][] = $cookieData;
        }

        $response = new JsonResponse($exportData);
        $response->headers->set('Content-Disposition', 'attachment; filename="cookie-consent-export.json"');

        return $response;
    }

    public function importAction(Request $request): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $stats = [
            'categoriesCreated' => 0,
            'categoriesUpdated' => 0,
            'cookiesCreated' => 0,
            'cookiesUpdated' => 0,
            'cookieItemsCreated' => 0,
            'cookieItemsUpdated' => 0,
        ];

        // Import categories
        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryData) {
                $result = $this->importCategory($categoryData);
                if ($result === 'created') {
                    $stats['categoriesCreated']++;
                } else {
                    $stats['categoriesUpdated']++;
                }
            }
        }

        $this->entityManager->flush();

        // Import cookies
        if (isset($data['cookies']) && is_array($data['cookies'])) {
            foreach ($data['cookies'] as $cookieData) {
                $result = $this->importCookie($cookieData, $stats);
                if ($result === 'created') {
                    $stats['cookiesCreated']++;
                } else {
                    $stats['cookiesUpdated']++;
                }
            }
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    private function importCategory(array $data): string
    {
        $category = $this->categoryRepository->findByTechnicalName($data['technicalName']);
        $isNew = false;

        if (!$category) {
            $category = new CookieCategory();
            $category->setTechnicalName($data['technicalName']);
            $this->entityManager->persist($category);
            $isNew = true;
        }

        $category->setEnabled($data['enabled'] ?? false);
        $category->setReadOnly($data['readOnly'] ?? false);
        $category->setPosition($data['position'] ?? 0);
        $category->setActive($data['active'] ?? true);

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $translationData) {
                $category->setLocale($locale);
                $category->setName($translationData['name'] ?? null);
                $category->setDescription($translationData['description'] ?? null);
            }
        }

        return $isNew ? 'created' : 'updated';
    }

    private function importCookie(array $data, array &$stats): string
    {
        $cookie = $this->cookieRepository->findByTechnicalName($data['technicalName']);
        $isNew = false;

        if (!$cookie) {
            $cookie = new Cookie();
            $cookie->setTechnicalName($data['technicalName']);
            $this->entityManager->persist($cookie);
            $isNew = true;
        }

        // Set category
        if (isset($data['categoryTechnicalName'])) {
            $category = $this->categoryRepository->findByTechnicalName($data['categoryTechnicalName']);
            if ($category) {
                $cookie->setCategory($category);
            }
        }

        $cookie->setProvider($data['provider'] ?? null);
        $cookie->setScriptUrl($data['scriptUrl'] ?? null);
        $cookie->setLegalBasis($data['legalBasis'] ?? Cookie::LEGAL_BASIS_CONSENT);
        $cookie->setProcessingLocation($data['processingLocation'] ?? Cookie::PROCESSING_LOCATION_EU);
        $cookie->setPosition($data['position'] ?? 0);
        $cookie->setActive($data['active'] ?? true);

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $translationData) {
                $cookie->setLocale($locale);
                $cookie->setName($translationData['name'] ?? null);
                $cookie->setDescription($translationData['description'] ?? null);
                $cookie->setPrivacyPolicyUrl($translationData['privacyPolicyUrl'] ?? null);
                $cookie->setDataCollected($translationData['dataCollected'] ?? null);
                $cookie->setDataPurpose($translationData['dataPurpose'] ?? null);
            }
        }

        // Import cookie items
        if (isset($data['cookieItems']) && is_array($data['cookieItems'])) {
            foreach ($data['cookieItems'] as $itemData) {
                $result = $this->importCookieItem($cookie, $itemData);
                if ($result === 'created') {
                    $stats['cookieItemsCreated']++;
                } else {
                    $stats['cookieItemsUpdated']++;
                }
            }
        }

        return $isNew ? 'created' : 'updated';
    }

    private function importCookieItem(Cookie $cookie, array $data): string
    {
        $item = null;
        $isNew = false;

        if ($cookie->getId()) {
            $item = $this->cookieItemRepository->findByNameAndCookie($data['name'], $cookie->getId());
        }

        if (!$item) {
            $item = new CookieItem();
            $item->setName($data['name']);
            $item->setCookie($cookie);
            $cookie->addCookieItem($item);
            $this->entityManager->persist($item);
            $isNew = true;
        }

        $item->setLifetime($data['lifetime'] ?? null);
        $item->setPosition($data['position'] ?? 0);
        $item->setActive($data['active'] ?? true);

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $translationData) {
                $item->setLocale($locale);
                $item->setDescription($translationData['description'] ?? null);
            }
        }

        return $isNew ? 'created' : 'updated';
    }
}

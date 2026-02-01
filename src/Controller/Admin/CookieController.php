<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use WSC\SuluCookieConsentBundle\Entity\Cookie;
use WSC\SuluCookieConsentBundle\Entity\CookieItem;
use WSC\SuluCookieConsentBundle\Repository\CookieCategoryRepository;
use WSC\SuluCookieConsentBundle\Repository\CookieRepository;

class CookieController extends AbstractRestController
{
    public function __construct(
        private CookieRepository $repository,
        private CookieCategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager,
        private DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private RestHelperInterface $restHelper,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function getListAction(Request $request): Response
    {
        $locale = $request->query->get('locale', 'de');
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Cookie::RESOURCE_KEY);

        $listBuilder = $this->listBuilderFactory->create(Cookie::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            Cookie::RESOURCE_KEY,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );

        return $this->handleView(View::create($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        $locale = $request->query->get('locale', 'de');
        $cookie = $this->repository->findById($id, $locale);

        if (!$cookie) {
            return $this->handleView(View::create(null, Response::HTTP_NOT_FOUND));
        }

        return $this->handleView(View::create($this->serialize($cookie, $locale)));
    }

    public function postAction(Request $request): Response
    {
        $locale = $request->query->get('locale', 'de');
        $data = $request->request->all();

        $cookie = new Cookie();
        $cookie->setLocale($locale);
        $this->mapDataToEntity($data, $cookie, $locale);

        $this->entityManager->persist($cookie);
        $this->entityManager->flush();

        return $this->handleView(View::create($this->serialize($cookie, $locale), Response::HTTP_CREATED));
    }

    public function putAction(int $id, Request $request): Response
    {
        $locale = $request->query->get('locale', 'de');
        $data = $request->request->all();

        $cookie = $this->repository->findById($id, $locale);
        if (!$cookie) {
            return $this->handleView(View::create(null, Response::HTTP_NOT_FOUND));
        }

        $cookie->setLocale($locale);
        $this->mapDataToEntity($data, $cookie, $locale);

        $this->entityManager->flush();

        return $this->handleView(View::create($this->serialize($cookie, $locale)));
    }

    public function deleteAction(int $id): Response
    {
        $cookie = $this->repository->find($id);

        if (!$cookie) {
            return $this->handleView(View::create(null, Response::HTTP_NOT_FOUND));
        }

        $this->entityManager->remove($cookie);
        $this->entityManager->flush();

        return $this->handleView(View::create(null, Response::HTTP_NO_CONTENT));
    }

    private function mapDataToEntity(array $data, Cookie $cookie, string $locale): void
    {
        // Handle category - single_selection sends just the ID
        $categoryId = $data['category'] ?? $data['categoryId'] ?? null;
        if ($categoryId !== null && is_numeric($categoryId)) {
            $category = $this->categoryRepository->find((int) $categoryId);
            if ($category) {
                $cookie->setCategory($category);
            }
        }
        if (isset($data['technicalName'])) {
            $cookie->setTechnicalName($data['technicalName']);
        }
        if (isset($data['provider'])) {
            $cookie->setProvider($data['provider']);
        }
        if (isset($data['scriptUrl'])) {
            $cookie->setScriptUrl($data['scriptUrl']);
        }
        if (isset($data['legalBasis'])) {
            $cookie->setLegalBasis($data['legalBasis']);
        }
        if (isset($data['processingLocation'])) {
            $cookie->setProcessingLocation($data['processingLocation']);
        }
        if (isset($data['position'])) {
            $cookie->setPosition((int) $data['position']);
        }
        if (isset($data['active'])) {
            $cookie->setActive((bool) $data['active']);
        }
        if (isset($data['name'])) {
            $cookie->setName($data['name']);
        }
        if (isset($data['description'])) {
            $cookie->setDescription($data['description']);
        }
        if (isset($data['privacyPolicyUrl'])) {
            $cookie->setPrivacyPolicyUrl($data['privacyPolicyUrl']);
        }
        if (isset($data['dataCollected'])) {
            $cookie->setDataCollected($data['dataCollected']);
        }
        if (isset($data['dataPurpose'])) {
            $cookie->setDataPurpose($data['dataPurpose']);
        }

        // Handle cookie items
        if (isset($data['cookieItems']) && is_array($data['cookieItems'])) {
            $this->updateCookieItems($cookie, $data['cookieItems'], $locale);
        }
    }

    private function updateCookieItems(Cookie $cookie, array $itemsData, string $locale): void
    {
        $existingItems = $cookie->getCookieItems()->toArray();
        $existingItemIds = array_map(fn(CookieItem $item) => $item->getId(), $existingItems);
        $updatedItemIds = [];

        foreach ($itemsData as $itemData) {
            if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                // Update existing item
                $item = $cookie->getCookieItems()->filter(
                    fn(CookieItem $i) => $i->getId() === (int) $itemData['id']
                )->first();

                if ($item) {
                    $item->setLocale($locale);
                    $this->mapItemData($item, $itemData);
                    $updatedItemIds[] = $item->getId();
                }
            } else {
                // Create new item
                $item = new CookieItem();
                $item->setLocale($locale);
                $item->setCookie($cookie);
                $this->mapItemData($item, $itemData);
                $cookie->addCookieItem($item);
            }
        }

        // Remove items that are no longer in the list
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem->getId(), $updatedItemIds) && $existingItem->getId() !== null) {
                $cookie->removeCookieItem($existingItem);
            }
        }
    }

    private function mapItemData(CookieItem $item, array $data): void
    {
        if (isset($data['name'])) {
            $item->setName($data['name']);
        }
        if (isset($data['lifetime'])) {
            $item->setLifetime($data['lifetime']);
        }
        if (isset($data['position'])) {
            $item->setPosition((int) $data['position']);
        }
        if (isset($data['active'])) {
            $item->setActive((bool) $data['active']);
        }
        if (isset($data['description'])) {
            $item->setDescription($data['description']);
        }
    }

    private function serialize(Cookie $cookie, string $locale): array
    {
        $cookie->setLocale($locale);

        $cookieItems = [];
        foreach ($cookie->getCookieItems() as $item) {
            $item->setLocale($locale);
            $cookieItems[] = [
                'type' => 'cookie_item', // Required for Sulu block type
                'id' => $item->getId(),
                'name' => $item->getName(),
                'lifetime' => $item->getLifetime(),
                'position' => $item->getPosition(),
                'active' => $item->isActive(),
                'description' => $item->getDescription(),
            ];
        }

        return [
            'id' => $cookie->getId(),
            'category' => $cookie->getCategory()?->getId(), // single_selection expects just the ID
            'categoryId' => $cookie->getCategory()?->getId(), // Keep for backwards compatibility
            'categoryName' => $cookie->getCategory()?->setLocale($locale)->getName(),
            'technicalName' => $cookie->getTechnicalName(),
            'provider' => $cookie->getProvider(),
            'scriptUrl' => $cookie->getScriptUrl(),
            'legalBasis' => $cookie->getLegalBasis(),
            'processingLocation' => $cookie->getProcessingLocation(),
            'position' => $cookie->getPosition(),
            'active' => $cookie->isActive(),
            'name' => $cookie->getName(),
            'description' => $cookie->getDescription(),
            'privacyPolicyUrl' => $cookie->getPrivacyPolicyUrl(),
            'dataCollected' => $cookie->getDataCollected(),
            'dataPurpose' => $cookie->getDataPurpose(),
            'cookieItems' => $cookieItems,
        ];
    }
}

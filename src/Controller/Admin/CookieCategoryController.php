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
use WSC\SuluCookieConsentBundle\Entity\CookieCategory;
use WSC\SuluCookieConsentBundle\Repository\CookieCategoryRepository;

class CookieCategoryController extends AbstractRestController
{
    public function __construct(
        private CookieCategoryRepository $repository,
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
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(CookieCategory::RESOURCE_KEY);

        $listBuilder = $this->listBuilderFactory->create(CookieCategory::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            CookieCategory::RESOURCE_KEY,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );

        return $this->handleView(View::create($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        $locale = $request->query->get('locale', 'de');
        $category = $this->repository->findById($id, $locale);

        if (!$category) {
            return $this->handleView(View::create(null, Response::HTTP_NOT_FOUND));
        }

        return $this->handleView(View::create($this->serialize($category, $locale)));
    }

    public function postAction(Request $request): Response
    {
        $locale = $request->query->get('locale', 'de');
        $data = $request->request->all();

        $category = new CookieCategory();
        $category->setLocale($locale);
        $this->mapDataToEntity($data, $category, $locale);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $this->handleView(View::create($this->serialize($category, $locale), Response::HTTP_CREATED));
    }

    public function putAction(int $id, Request $request): Response
    {
        $locale = $request->query->get('locale', 'de');
        $data = $request->request->all();

        $category = $this->repository->findById($id, $locale);
        if (!$category) {
            return $this->handleView(View::create(null, Response::HTTP_NOT_FOUND));
        }

        $category->setLocale($locale);
        $this->mapDataToEntity($data, $category, $locale);

        $this->entityManager->flush();

        return $this->handleView(View::create($this->serialize($category, $locale)));
    }

    public function deleteAction(int $id): Response
    {
        $category = $this->repository->find($id);

        if (!$category) {
            return $this->handleView(View::create(null, Response::HTTP_NOT_FOUND));
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return $this->handleView(View::create(null, Response::HTTP_NO_CONTENT));
    }

    private function mapDataToEntity(array $data, CookieCategory $category, string $locale): void
    {
        if (isset($data['technicalName'])) {
            $category->setTechnicalName($data['technicalName']);
        }
        if (isset($data['enabled'])) {
            $category->setEnabled((bool) $data['enabled']);
        }
        if (isset($data['readOnly'])) {
            $category->setReadOnly((bool) $data['readOnly']);
        }
        if (isset($data['position'])) {
            $category->setPosition((int) $data['position']);
        }
        if (isset($data['active'])) {
            $category->setActive((bool) $data['active']);
        }
        if (isset($data['name'])) {
            $category->setName($data['name']);
        }
        if (isset($data['description'])) {
            $category->setDescription($data['description']);
        }
    }

    private function serialize(CookieCategory $category, string $locale): array
    {
        $category->setLocale($locale);

        return [
            'id' => $category->getId(),
            'technicalName' => $category->getTechnicalName(),
            'enabled' => $category->isEnabled(),
            'readOnly' => $category->isReadOnly(),
            'position' => $category->getPosition(),
            'active' => $category->isActive(),
            'name' => $category->getName(),
            'description' => $category->getDescription(),
        ];
    }
}

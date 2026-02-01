<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WSC\SuluCookieConsentBundle\Entity\CookieCategory;

/**
 * @extends ServiceEntityRepository<CookieCategory>
 */
class CookieCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CookieCategory::class);
    }

    public function findById(int $id, string $locale): ?CookieCategory
    {
        $category = $this->find($id);
        if ($category) {
            $category->setLocale($locale);
        }
        return $category;
    }

    public function findByTechnicalName(string $technicalName): ?CookieCategory
    {
        return $this->findOneBy(['technicalName' => $technicalName]);
    }

    /**
     * @return CookieCategory[]
     */
    public function findAllActive(string $locale): array
    {
        $categories = $this->createQueryBuilder('c')
            ->where('c.active = :active')
            ->setParameter('active', true)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($categories as $category) {
            $category->setLocale($locale);
        }

        return $categories;
    }

    /**
     * @return CookieCategory[]
     */
    public function findAllForLocale(string $locale): array
    {
        return $this->findAllForList($locale);
    }

    /**
     * @return CookieCategory[]
     */
    public function findAllForList(string $locale): array
    {
        $categories = $this->createQueryBuilder('c')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($categories as $category) {
            $category->setLocale($locale);
        }

        return $categories;
    }

    public function save(CookieCategory $category): void
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    public function remove(CookieCategory $category): void
    {
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }
}

<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WSC\SuluCookieConsentBundle\Entity\Cookie;

/**
 * @extends ServiceEntityRepository<Cookie>
 */
class CookieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cookie::class);
    }

    public function findById(int $id, string $locale): ?Cookie
    {
        $cookie = $this->find($id);
        if ($cookie) {
            $cookie->setLocale($locale);
        }
        return $cookie;
    }

    public function findByTechnicalName(string $technicalName): ?Cookie
    {
        return $this->findOneBy(['technicalName' => $technicalName]);
    }

    /**
     * @return Cookie[]
     */
    public function findAllActive(string $locale): array
    {
        $cookies = $this->createQueryBuilder('c')
            ->join('c.category', 'cat')
            ->where('c.active = :active')
            ->andWhere('cat.active = :catActive')
            ->setParameter('active', true)
            ->setParameter('catActive', true)
            ->orderBy('cat.position', 'ASC')
            ->addOrderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($cookies as $cookie) {
            $cookie->setLocale($locale);
        }

        return $cookies;
    }

    /**
     * @return Cookie[]
     */
    public function findByCategoryId(int $categoryId, string $locale): array
    {
        $cookies = $this->createQueryBuilder('c')
            ->where('c.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($cookies as $cookie) {
            $cookie->setLocale($locale);
        }

        return $cookies;
    }

    /**
     * @return Cookie[]
     */
    public function findAllForList(string $locale): array
    {
        $cookies = $this->createQueryBuilder('c')
            ->join('c.category', 'cat')
            ->orderBy('cat.position', 'ASC')
            ->addOrderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($cookies as $cookie) {
            $cookie->setLocale($locale);
        }

        return $cookies;
    }

    public function save(Cookie $cookie): void
    {
        $this->getEntityManager()->persist($cookie);
        $this->getEntityManager()->flush();
    }

    public function remove(Cookie $cookie): void
    {
        $this->getEntityManager()->remove($cookie);
        $this->getEntityManager()->flush();
    }
}

<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WSC\SuluCookieConsentBundle\Entity\CookieItem;

/**
 * @extends ServiceEntityRepository<CookieItem>
 */
class CookieItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CookieItem::class);
    }

    public function findById(int $id, string $locale): ?CookieItem
    {
        $item = $this->find($id);
        if ($item) {
            $item->setLocale($locale);
        }
        return $item;
    }

    public function findByNameAndCookie(string $name, int $cookieId): ?CookieItem
    {
        return $this->createQueryBuilder('ci')
            ->where('ci.name = :name')
            ->andWhere('ci.cookie = :cookieId')
            ->setParameter('name', $name)
            ->setParameter('cookieId', $cookieId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return CookieItem[]
     */
    public function findByCookieId(int $cookieId, string $locale): array
    {
        $items = $this->createQueryBuilder('ci')
            ->where('ci.cookie = :cookieId')
            ->andWhere('ci.active = :active')
            ->setParameter('cookieId', $cookieId)
            ->setParameter('active', true)
            ->orderBy('ci.position', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $item->setLocale($locale);
        }

        return $items;
    }

    public function save(CookieItem $item): void
    {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    public function remove(CookieItem $item): void
    {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}

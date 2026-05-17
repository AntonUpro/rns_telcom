<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AppendixStaticImage;
use App\Enum\AppendixTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppendixStaticImage>
 */
class AppendixStaticImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppendixStaticImage::class);
    }

    /** @return AppendixStaticImage[] */
    public function findByType(AppendixTypeEnum $type): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.appendixType = :type')
            ->setParameter('type', $type->value)
            ->orderBy('a.position', 'ASC')
            ->addOrderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Возвращает все изображения, сгруппированные по типу приложения.
     *
     * @return array<string, AppendixStaticImage[]>
     */
    public function findAllGroupedByType(): array
    {
        $all = $this->createQueryBuilder('a')
            ->orderBy('a.appendixType', 'ASC')
            ->addOrderBy('a.position', 'ASC')
            ->addOrderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($all as $image) {
            $grouped[$image->getAppendixType()->value][] = $image;
        }

        return $grouped;
    }
}

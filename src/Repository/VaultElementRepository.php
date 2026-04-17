<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\VaultElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VaultElement>
 */
class VaultElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VaultElement::class);
    }

    /**
     * Rechercher les éléments du coffre d'un utilisateur
     * Filtrage par titre et/ou par type
     */
    public function findByUserWithFilters(User $user, ?string $search = null, ?string $type = null): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.createdBy = :user')
            ->setParameter('user', $user)
            ->orderBy('v.createdAt', 'DESC');

        // Filtre par titre (recherche partielle insensible à la casse)
        if ($search) {
            $qb->andWhere('LOWER(v.title) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par type
        if ($type) {
            $qb->andWhere('v.type = :type')
                ->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }
}
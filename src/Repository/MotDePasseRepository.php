<?php

/*
 * g.ponty@dev-web.io
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MotDePasse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MotDePasse|null find($id, $lockMode = null, $lockVersion = null)
 * @method MotDePasse|null findOneBy(array $criteria, array $orderBy = null)
 * @method MotDePasse[]    findAll()
 * @method MotDePasse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotDePasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MotDePasse::class);
    }
}

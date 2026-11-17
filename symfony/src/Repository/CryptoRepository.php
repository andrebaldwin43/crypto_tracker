<?php

namespace App\Repository;

use App\Document\Crypto;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\Persistence\ManagerRegistry;

class CryptoRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crypto::class);
    }

    public function findAllByUser($user)
    {
        return $this->createQueryBuilder()
            ->field('user')->equals($user)
            ->getQuery()
            ->execute();
    }
}
<?php

namespace Repository;

use Entity\User;
use Lib\Database\EntityManager\EntityManager;

class UserRepository
{
    public function __construct(protected EntityManager $entityManager) {}

    public function findBy(array $where) : ?User
    {
        $user = $this->entityManager->findBy(User::class, $where);

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    public function findOrFailBy(array $where) : User
    {
        $user = $this->entityManager->findOrFailBy(User::class, $where);

        if (!$user instanceof User) {
            throw new \RuntimeException('Return type must be ' . User::class . ', return ' . get_class($user));
        }

        return $user;
    }

    public function create() : User
    {
        return new User();
    }
}
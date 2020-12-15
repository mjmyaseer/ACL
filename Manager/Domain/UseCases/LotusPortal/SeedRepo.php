<?php

namespace Manager\Domain\UseCases\LotusPortal;

Use Manager\Data\Repositories\Contracts\UserRepoInterface;

class SeedRepo
{
    /**
     * @var UserRepoInterface
     */
    private $user;

    public function __construct(
        UserRepoInterface $user
    )
    {
        $this->user = $user;
    }

    public function getAllSeeds($admin) {
        
        return $this->user->getAllSeeds($admin);
        
    }   

    public function getUserByID($id) {
        return $this->user->getUserByID($id);
    }

}
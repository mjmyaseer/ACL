<?php


namespace Manager\Domain\UseCases\LotusPortal;

Use Manager\Data\Repositories\Contracts\AdminUserRepoInterface;

class AdminUser
{
    /**
     * @var AdminUserRepoInterface
     */
    private $adminUser;

    public function __construct(
        AdminUserRepoInterface $adminUser
    )
    {
        $this->adminUser = $adminUser;
    }

    public function index($search) {
        return $this->adminUser->index($search);
    }

    public function getUserByID($id) {
        return $this->adminUser->getUserByID($id);
    }

}

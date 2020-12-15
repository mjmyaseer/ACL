<?php


namespace Manager\Domain\UseCases\LotusPortal;

Use Manager\Data\Repositories\Contracts\AdminUserRepoInterface;

class AdminUser
{
    /**
     * @var AppMenuRepoInterface
     */
    private $adminUser;

    public function __construct(
        AppMenuRepoInterface $adminUser
    )
    {
        $this->adminUser = $adminUser;
    }

    public function index($search) {
        return $this->adminUser->index($search);
    }

}

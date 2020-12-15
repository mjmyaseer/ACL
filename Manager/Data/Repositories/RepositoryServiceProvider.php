<?php

namespace Manager\Data\Repositories;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            \Manager\Data\Repositories\Contracts\StoreInterface::class,
            \Manager\Data\Repositories\MySQL\StoreRepo::class
        );
    }
}

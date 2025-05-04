<?php

namespace App\Providers;

use App\Repositories\Base\BaseRepository;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Implementations\PermissionRepository;
use App\Repositories\Implementations\RoleRepository;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        foreach ($this->getModels() as $model) {
            $this->app->bind("App\\Repositories\\Interfaces\\{$model}RepositoryInterface",
                "App\\Repositories\\Implementations\\{$model}Repository");
        }
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    private function getModels(): Collection
    {
        $modelFiles = File::allFiles(app_path('Models'));
        return collect($modelFiles)->map(function ($file) {
            return basename($file->getFilename(), '.php');
        });
    }
}

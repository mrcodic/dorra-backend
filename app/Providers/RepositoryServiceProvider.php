<?php

namespace App\Providers;

use App\Repositories\Base\BaseRepository;
use App\Repositories\Base\BaseRepositoryInterface;
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

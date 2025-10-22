<?php

namespace App\Providers;

use App\Support\MenuAuthorizer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('*', function ($view) {
            // Load raw JSON
            $verticalMenuJson   = file_get_contents(base_path('resources/data/menu-data/verticalMenu.json'));
            $horizontalMenuJson = file_get_contents(base_path('resources/data/menu-data/horizontalMenu.json'));

            $verticalMenuData   = json_decode($verticalMenuJson);
            $horizontalMenuData = json_decode($horizontalMenuJson);

            // Filter by current user's permissions
            $user = auth()->user();
            $verticalMenuData   = MenuAuthorizer::filter($verticalMenuData, $user);
            $horizontalMenuData = MenuAuthorizer::filter($horizontalMenuData, $user); // optional

            // Share same variable name/shape used by your Blade
            $view->with('menuData', [$verticalMenuData, $horizontalMenuData]);
        });
    }
}

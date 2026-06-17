<?php
namespace App\Providers;
use App\Support\MenuAuthorizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
class MenuServiceProvider extends ServiceProvider
{
    public function register() {}
    public function boot()
    {
        View::composer('panels.*', function ($view) {
            $verticalMenuJson   = file_get_contents(base_path('resources/data/menu-data/verticalMenu.json'));
            $horizontalMenuJson = file_get_contents(base_path('resources/data/menu-data/horizontalMenu.json'));
            $verticalMenuData   = json_decode($verticalMenuJson);
            $horizontalMenuData = json_decode($horizontalMenuJson);
            // ✅ Inject dynamic product tabs under Templates
            $verticalMenuData = $this->injectCategoryTemplates($verticalMenuData);
            $user = auth()->user();
            $verticalMenuData   = MenuAuthorizer::filter($verticalMenuData, $user);
            $horizontalMenuData = MenuAuthorizer::filter($horizontalMenuData, $user);
            $view->with('menuData', [$verticalMenuData, $horizontalMenuData]);
        });
    }
    private function injectCategoryTemplates(object $menuData): object
    {
        static $categories = null;

        if ($categories === null) {
            $categories = cache()->remember('menu_categories', now()->addMinutes(10), function () {
                return \App\Models\Category::select('id', 'name')->orderBy('sort')->get();
            });
        }

        foreach ($menuData->menu as $item) {
            if ($item->name === 'Templates') {
                if (!isset($item->submenu)) {
                    $item->submenu = [];
                }

                foreach ($categories as $category) {
                    $item->submenu[] = (object) [
                        'url'  => '/product-templates?product_without_category_id=' . $category->id,
                        'name' => $category->name,
                        'icon' => 'circle',
                        'slug' => '',
                        'dynamic' => true,
                    ];
                }

                break;
            }
        }

        return $menuData;
    }
}

<?php

namespace App\Services;


use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10)
    {
        $query = $this->repository->query()
            ->with($relations)
            ->when(request()->filled('is_landing'), function ($query) {
            $query->where('is_landing', true);
        });

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    public function getSubCategories()
    {
        return $this->repository->getWithFilters();
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $categories = $this->repository
            ->query(['id', 'name', 'description', 'created_at'])
            ->with(['products', 'children'])
            ->withCount(['children', 'products'])
            ->when(request()->filled('search_value'), function ($query) use ( $locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower(request('search_value')) . '%'
                ]);
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->whereNull('parent_id')
            ->latest();

        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                return $category->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($category) {
                return $category->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($category) {
                return $category->getTranslation('name', 'ar');
            })
            ->addColumn('description_en', function ($category) {
                return $category->getTranslation('description', 'en');
            })
            ->addColumn('description_ar', function ($category) {
                return $category->getTranslation('description', 'ar');
            })
            ->addColumn('image', function ($category) {
                return $category->getFirstMediaUrl('categories');
            })
            ->addColumn('imageId', function ($category) {
                return $category->getFirstMedia('categories')?->id;
            })
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($category) {
                return $category->created_at?->format('Y-m-d');
            })
            ->addColumn('sub_categories', function ($category) {
                return $category->children_count;
            })
            ->addColumn('no_of_products', function ($category) {
                return $category->products_count;
            })
            ->make(true);
    }


    public function getSubCategoryData(): JsonResponse
    {
        $locale = app()->getLocale();
        $categories = $this->repository
            ->query(['id', 'name', 'parent_id', 'created_at'])
            ->with(['parent'])
            ->withCount(['subCategoryProducts'])
            ->whereNotNull('parent_id')
            ->when(request()->filled('search_value'), function ($query) use ( $locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower(request('search_value')) . '%'
                ]);
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();
        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                return $category->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($category) {
                return $category->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($category) {
                return $category->getTranslation('name', 'ar');
            })
            ->addColumn('parent_name', function ($category) {
                return $category->parent->getTranslation('name', app()->getLocale());
            })
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($category) {
                return $category->created_at?->format('Y-m-d');
            })
            ->addColumn('no_of_products', function ($category) {
                return $category->products_count;
            })->make();
    }

    public function search($request)
    {
        $locale = App::getLocale();
        return $this->repository->query()
            ->when($request->filled('search'), function ($query) use ($request, $locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower($request->search) . '%'
                ]);
            })->get();
    }

    public function addToLanding($categoryId)
    {
        if ($this->repository->query()->isLanding()->count() == 7) {
            throw ValidationException::withMessages([
                'category_id' => ['you can\'t add more than 7 items.']
            ]);
        }
        $category = $this->repository->find($categoryId);
        return tap($category, function ($category) {
            $category->update(['is_landing' => true]);
        });
    }

    public function removeFromLanding($categoryId)
    {
        $category = $this->repository->find($categoryId);
        return tap($category, function ($category) {
            $category->update(['is_landing' => false]);
        });
    }

}

<?php

namespace App\Services;

use App\Repositories\Interfaces\TemplateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class TemplateService extends BaseService
{

    public function __construct(TemplateRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getData(): JsonResponse
    {
        $templates = $this->repository
            ->query(['id', 'name', 'preview_png', 'product_id', 'updated_at'])
            ->with(['product:id,name'])
            ->latest();

        return DataTables::of($templates)
            ->addColumn('name', function ($template) {
                return $template->getTranslation('name', app()->getLocale());
            })
            ->addColumn('status', function ($template) {
                return $template->status?->label() ?? '';
            })
            ->make(true);
    }


}

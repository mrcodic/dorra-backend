<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class FaqService extends BaseService
{

    public function __construct(FaqRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }



    public function getData()
    {
        $faqs = $this->repository->query()
            ->when(request()->filled('search_value'), function ($query) {
                $search = request('search_value');
                $words = preg_split('/\s+/', $search);
                $query->where(function ($query) use ($words) {
                    foreach ($words as $word) {
                        $query->where(function ($q) use ($word) {
                            $q->where('question', 'like', '%' . $word . '%')
                                ->orWhere('answer', 'like', '%' . $word . '%');
                        });
                    }
                });
            })
            ->orderBy('created_at', request('created_at', 'desc'));

        return DataTables::of($faqs)
            ->editColumn('question', function ($faq) {
                return $faq->getTranslation('question', app()->getLocale());
            })->addColumn('question_en', function ($faq) {
                return $faq->getTranslation('question', 'en');
            })->addColumn('question_ar', function ($faq) {
                return $faq->getTranslation('question', 'ar');
            })->addColumn('answer_en', function ($faq) {
                return $faq->getTranslation('answer', 'en');
            })->addColumn('answer_ar', function ($faq) {
                return $faq->getTranslation('answer', 'ar');
            })
            ->editColumn('created_at', function ($faq) {
                return $faq->created_at->format('d/m/Y') ;
            })
            ->make();
    }

}

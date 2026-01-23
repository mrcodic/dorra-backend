<?php

namespace App\Services;


use App\DTOs\Payment\Fawry\PaymentRequestData;
use App\Repositories\Implementations\PaymentMethodRepository;
use App\Repositories\Interfaces\PlanRepositoryInterface;
use App\Services\Payment\PaymentGatewayFactory;
use Yajra\DataTables\Facades\DataTables;

class PlanService extends BaseService
{

    public function __construct(PlanRepositoryInterface        $repository,
                                public PaymentMethodRepository $paymentMethodRepository,
                                public PaymentGatewayFactory   $paymentFactory,

    )
    {
        parent::__construct($repository);

    }

    public function subscribe($validateData)
    {
        $plan = $this->repository->find($validateData['plan_id']);
        $paymentMethod = $this->paymentMethodRepository->find($validateData['payment_method_id']);
        $gatewayCode = $paymentMethod->paymentGateway->code;
        $paymentGatewayStrategy = $this->paymentFactory->make($gatewayCode);
        $dto = PaymentRequestData::fromArray([
            'plan' => $plan,
            'user' => auth()->user(),
            'method' => $paymentMethod->code,
        ]);

      return  $paymentGatewayStrategy->pay($dto->toArray(),['plan' => $plan,'type' => 'plan']);

    }

    public function activePlans()
    {
        return $this->repository->query()->whereIsActive(true)->get();
    }

    public function getData()
    {
        $plans = $this->repository
            ->query()
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $words = preg_split('/\s+/', $search);
                    $query->where(function ($query) use ($words) {
                        foreach ($words as $word) {
                            $query->where('name', 'like', '%' . $word . '%');
                        }
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('status'), function ($query) {
                $query->whereIsActive(request()->input('status'));
            })
            ->orderBy('created_at', request('created_at', 'desc'));

        return DataTables::of($plans)
            ->editColumn('created_at', function ($plan) {
                return $plan->created_at->format('d/m/Y');
            })
            ->addColumn('action', function ($plan) {
                return [
                    'can_edit' => (bool)auth()->user()->hasPermissionTo('plans_update'),
                    'can_delete' => (bool)auth()->user()->hasPermissionTo('plans_delete'),
                ];
            })
            ->make();
    }

}

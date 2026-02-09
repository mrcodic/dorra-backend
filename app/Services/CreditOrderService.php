<?php

namespace App\Services;

use App\Enums\CreditOrder\StatusEnum;
use App\Models\Plan;
use App\Models\User;
use App\Repositories\Interfaces\CreditOrderRepositoryInterface;
use App\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;


class CreditOrderService extends BaseService
{
    public function __construct(CreditOrderRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {

        $creditOrders = $this->repository
            ->query()
            ->with(['plan', 'user'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $query->where("number", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();

        return DataTables::of($creditOrders)
            ->addColumn('added_date', function ($creditOrder) {
                return $creditOrder->created_at?->format('d/n/Y');
            })->editColumn('status', function ($creditOrder) {
                return [
                    'value' => $creditOrder->status->value,
                    'label' => $creditOrder->status->label(),
                    'badgeClass' => $creditOrder->status->badgeClass(),
                ];
            })
            ->addColumn('action', function () {
                return [
                    'can_delete' => (bool)auth()->user()->hasPermissionTo('credit-orders_delete'),
                ];
            })
            ->make(true);
    }

    /**
     * @throws \Exception
     */
    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
      return  $this->handleTransaction(function () use ($validatedData, $relationsToStore, $relationsToLoad) {
            $plan = Plan::find($validatedData['plan_id']);
            $user = User::find($validatedData['user_id']);

            $validatedData['credits'] = $plan->credits;
            $validatedData['amount'] = $plan->price;
            $validatedData['status'] = StatusEnum::PAID;
            $resource = parent::storeResource($validatedData, $relationsToStore, $relationsToLoad);
            WalletService::credit($user, $plan->credits, 'purchase_by_admin');
            return $resource;
        });

    }
}

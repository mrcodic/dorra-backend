<?php

namespace App\Http\Controllers\Api\V1\User\Payment;

use App\DTOs\Payment\PaymentRequestData;
use App\Enums\Payment\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Transaction;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PaymentController extends Controller
{
    public function __construct(public PaymentMethodRepositoryInterface $paymentMethodRepository,
                                public PaymentGatewayFactory            $paymentFactory,
                                public OrderRepositoryInterface         $orderRepository,
    ){}

    public function paymentMethods()
    {
        return Response::api(data: PaymentResource::collection($this->paymentMethodRepository->all()));
    }

//    public function getPaymentLink(Request $request)
//    {
//        $request->validate([
//            'payment_method_id' => ['required', 'exists:payment_methods,id'],
//            'order_id' => ['required', 'exists:orders,id'],
//        ]);
//        $selectedOrder = $this->orderRepository->find($request->get('order_id'));
//        $selectedPaymentMethod = $this->paymentMethodRepository->find($request->payment_method_id);
//        $paymentGatewayStrategy = $this->paymentFactory->make($selectedPaymentMethod->paymentGateway->code ?? 'paymob');
//        $dto = PaymentRequestData::fromArray(['order' => $selectedOrder, 'user' => auth('sanctum')->user(), 'method' => $selectedPaymentMethod]);
//        $paymentDetails = $paymentGatewayStrategy->pay($dto->toArray(),['order' => $selectedOrder, 'user' => auth('sanctum')->user()]);
//        return Response::api(data: $paymentDetails);
//    }

    public function handleCallback(Request $request)
    {
        $data = $request->json()->all();
        $paymentMethod = data_get($data, 'obj.source_data.sub_type');
        $paymobOrderId = data_get($data, 'obj.order.id');
        $isSuccess = data_get($data, 'obj.success');
        $isPending = data_get($data, 'obj.pending');

        if (!$paymobOrderId) {
            return response()->json(['error' => 'Missing order ID'], 400);
        }
        $transaction = Transaction::where('transaction_id', $paymobOrderId)->firstOrFail();


        if ($isSuccess && !$isPending) {
            $paymentStatus = StatusEnum::PAID;
        } elseif (!$isSuccess && $isPending) {
            $paymentStatus = StatusEnum::PENDING;
        } elseif (!$isSuccess && !$isPending) {
            $paymentStatus = StatusEnum::UNPAID;
        }
        Log::info('Failed to create payment intention', [
            'paymobOrderId' => $paymobOrderId,
          'status' => $paymentStatus,
        ]);
        $transaction->update([
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'response_message' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);
        return Response::api();
    }
    public function handleRedirect(Request $request): RedirectResponse
    {
        $requestedData = $request->all();
        $paymobOrderId = data_get($requestedData, 'order');
        $success = data_get($requestedData, 'success') == 'true';
        $pending = data_get($requestedData, 'pending') == 'true';
        $transaction = Transaction::whereTransactionId($paymobOrderId)->first();
        if (!$transaction) {
            return redirect()->to(config('services.frontend_base_url'));
        } else {
            if ($success) {
                return redirect()->to($transaction->success_url);
            }
            if ($pending) {
                return redirect()->to($transaction->pending_url);
            }
            return redirect()->to($transaction->failure_url);
        }

    }
}

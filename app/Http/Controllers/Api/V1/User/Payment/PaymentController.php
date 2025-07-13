<?php

namespace App\Http\Controllers\Api\V1\User\Payment;

use App\DTOs\Payment\PaymentRequestData;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Http\Request;
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

    public function getPaymentLink(Request $request)
    {
        $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'order_id' => ['required', 'exists:orders,id'],
        ]);
        $selectedOrder = $this->orderRepository->find($request->get('order_id'));
        $selectedPaymentMethod = $this->paymentMethodRepository->find($request->payment_method_id);
        $paymentGatewayStrategy = $this->paymentFactory->make($selectedPaymentMethod->paymentGateway->code ?? 'paymob');
        $dto = PaymentRequestData::fromArray(['order' => $selectedOrder, 'user' => auth('sanctum')->user(), 'method' => $selectedPaymentMethod]);
        $paymentDetails = $paymentGatewayStrategy->pay($dto->toArray(),['order' => $selectedOrder, 'user' => auth('sanctum')->user()]);
        return Response::api(data: $paymentDetails);
    }

    public function handleWebhook(Request $request)
    {
        dd($request->all());
    }
    public function handleCallback(Request $request)
    {
        dd($request->all());
    }
}

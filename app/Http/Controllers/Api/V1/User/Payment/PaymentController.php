<?php

namespace App\Http\Controllers\Api\V1\User\Payment;

use App\DTOs\Payment\PaymentRequestData;
use App\Enums\HttpEnum;
use App\Enums\Payment\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Guest;
use App\Models\Transaction;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use App\Services\CartService;
use App\Services\Payment\PaymentGatewayFactory;
use App\Traits\HandlesTryCatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PaymentController extends Controller
{
    use HandlesTryCatch;

    public function __construct(public PaymentMethodRepositoryInterface $paymentMethodRepository,
                                public PaymentGatewayFactory            $paymentFactory,
                                public OrderRepositoryInterface         $orderRepository,
    )
    {
    }

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
        $selectedOrder = $this->orderRepository->query()->with(['orderItems.specs', 'orderAddress', 'pickupContact']);
        $newOrder = $selectedOrder->replicate();
        $newOrder->isReplication = true;
        $newOrder->originalOrder = $selectedOrder;
        $dateString = now()->format('d-m-Y');
        $newOrder->order_number = "#ORD-{$dateString}-" . mt_rand(100, 999);
        $newOrder->save();

        $selectedPaymentMethod = $this->paymentMethodRepository->find($request->payment_method_id);
        $paymentGatewayStrategy = $this->paymentFactory->make($selectedPaymentMethod->paymentGateway->code ?? 'paymob');
        $dto = PaymentRequestData::fromArray(['order' => $newOrder, 'user' => auth('sanctum')->user(),
            'guest' => Guest::query()->whereCookieValue('cookie_value')->first(), 'method' => $selectedPaymentMethod]);
        $paymentDetails = $paymentGatewayStrategy->pay($dto->toArray(), ['order' => $selectedOrder, 'user' => auth('sanctum')->user()]);
        if (!$paymentDetails) {
            return Response::api(HttpEnum::BAD_REQUEST,
                message: 'Something went wrong',
                errors: [
                    'error' => ['Failed to payment transaction try again later.'],
                ]
            );
        }
        return Response::api(data: $paymentDetails);
    }

    public function handleCallback(Request $request, CartService $cartService)
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
        $this->handleTransaction(function () use ($transaction, $paymentMethod, $paymentStatus,$data, $cartService) {
            $cart = $cartService->getCurrentUserOrGuestCart();
            $cart?->items()->delete();
            if ($cart && $cart->discountCode) {
                $cart->discountCode->increment('used');
            }
            $cart?->update(['price' => 0, 'discount_amount' => 0, 'discount_code_id' => null]);
            $transaction->update([
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'response_message' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);
        });

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

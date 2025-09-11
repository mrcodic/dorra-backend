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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PaymentController extends Controller
{
    use HandlesTryCatch;

    public function __construct(public PaymentMethodRepositoryInterface $paymentMethodRepository,
                                public PaymentGatewayFactory            $paymentFactory,
                                public OrderRepositoryInterface         $orderRepository,
                                public CartService                      $cartService,
    )
    {
    }

    public function paymentMethods()
    {
        return Response::api(data: PaymentResource::collection($this->paymentMethodRepository->all(relations: ['paymentGateway'])));
    }

    public function buyOrderAgain(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
        ]);

        $this->handleTransaction(function () use ($request) {
            $order = $this->orderRepository->query()->with('orderItems.specs')->find($request->get('order_id'));
            $cart = $this->cartService->getCurrentUserOrGuestCart();

            collect($order->orderItems)->each(function ($orderItem) use ($cart) {
                $existingCartItem = $cart->items()
                    ->where('cartable_id', $orderItem->orderable_id)
                    ->first();
                if (!$existingCartItem) {
                    if ($orderItem->orderable->has_custom_prices) {
                        $subTotal = ($orderItem->productPrice?->price ?? $orderItem->product_price)
                            + ($orderItem->specs->sum(function ($spec) {
                                return $spec->productSpecificationOption->price;
                            }) ?: $orderItem->specs_price);
                    } else {
                        $subTotal = (
                                ($orderItem->product->base_price ?? $orderItem->product_price)
                                + ($orderItem->specs->sum(function ($spec) {
                                    return $spec->productSpecificationOption->price;
                                }) ?: $orderItem->specs_price)
                            ) * $orderItem->quantity;
                    }

                    $cartItem = $cart->items()->create([
                        'cartable_id' => $orderItem->product_id,
                        'product_price_id' => $orderItem->product_price_id,
                        'product_price' => $orderItem->productPrice?->price ?? $orderItem->product_price,
                        'specs_price' => ($orderItem->specs->sum(function ($spec) {
                            return $spec->productSpecificationOption->price;
                        }) ?: $orderItem->specs_price),
                        'quantity' => $orderItem->quantity,
                        'sub_total' => $subTotal,
                        'itemable_type' => $orderItem->itemable_type,
                        'itemable_id' => $orderItem->itemable_id,
                    ]);


                    $cartItem->specs()->createMany($orderItem->specs->toArray());
                }
            });
        });

        return Response::api();
    }


    /**
     * @throws \Exception
     */
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
            $this->resetCart($transaction, $paymentMethod, $paymentStatus, $data);
        } elseif (!$isSuccess && $isPending) {
            $paymentStatus = StatusEnum::PENDING;
            Log::info('Deleting order', ['order' => $transaction->order]);

            $transaction->order?->forceDelete();
            $transaction->update([
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'response_message' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

        } elseif (!$isSuccess && !$isPending) {
            $paymentStatus = StatusEnum::UNPAID;
            Log::info('dgfdgd order', ['order' => $transaction->order]);

            $transaction->order?->forceDelete();
        }

        Log::info('Failed to create payment intention', [
            'paymobOrderId' => $paymobOrderId,
            'status' => $paymentStatus,
        ]);


        // Update transaction
        $transaction->update([
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'response_message' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);
        return Response::api();
    }

    /**
     * @param $transaction
     * @param mixed $paymentMethod
     * @param StatusEnum $paymentStatus
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function resetCart($transaction, mixed $paymentMethod, StatusEnum $paymentStatus, array $data): void
    {
        $this->handleTransaction(function () use ($transaction, $paymentMethod, $paymentStatus, $data) {
            $cart = $transaction->order->user?->cart ?? $transaction->order->guest?->cart;
            if ($cart) {
                $cart->items()->delete();

                if ($cart->discountCode) {
                    $cart->discountCode->increment('used');
                }

                $cart->update([
                    'price' => 0,
                    'discount_amount' => 0,
                    'discount_code_id' => null,
                ]);
            }


        });
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

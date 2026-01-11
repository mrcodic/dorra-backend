<?php

namespace App\Http\Controllers\Api\V1\User\Order;


use App\Enums\HttpEnum;
use App\Enums\Order\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Checkout\CheckoutRequest;
use App\Http\Resources\LocationResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\OrderItem;
use App\Services\LocationService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use ZipArchive;


class OrderController extends Controller
{
    public function __construct(public OrderService $orderService, public LocationService $locationService){}

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:' . StatusEnum::getValuesAsString()]
        ]);
        return Response::api(data: OrderResource::collection($this->orderService->userOrders())->response()->getData(true));

    }

    public function show($id)
    {
        return Response::api(data: OrderResource::make($this->orderService->showUserOrder($id)));
    }

    public function checkout(CheckoutRequest $request)
    {
        $order = $this->orderService->checkout($request);
        if (Arr::get($order,'paymentDetails') === false)
        {
            return Response::api(HttpEnum::BAD_REQUEST,
                message: __('orders.payment_failed_message'),
                errors: [
                    'error' => [__('orders.payment_failed_error')],
                ]
            );
        }
        if (!$order) {
            return Response::api(statusCode: HttpEnum::BAD_REQUEST, message: 'Bad request', errors: ['message' => ['Cart is empty.']]);
        }
        return Response::api(data: $order);
    }

    public function searchLocations(Request $request)
    {
        $locations = $this->locationService->search($request);
        return Response::api(data: LocationResource::collection($locations->load('state.country')));
    }

    public function trackOrder($id)
    {
        $order = $this->orderService->trackOrder($id);
        return Response::api(data: OrderResource::make($order->load(['orderAddress', 'orderItems'])));

    }

    public function orderStatuses()
    {
        return Response::api(data: $this->orderService->orderStatuses());
    }

    public function cancelOrder($id)
    {
     $this->orderService->cancelOrder($id);
    }

    public function downloadItem(OrderItem $orderItem, Request $request)
    {
        $data = $request->validate([
            'format' => ['required', 'in:jpg,png'],
        ]);

        $format   = $data['format'];
        $itemable = $orderItem->itemable;

        $sides = $itemable->types
            ->map(fn ($type) => strtolower($type->value->key()))
            ->unique()
            ->values();

        $mediaFront = $itemable->getFirstMedia('templates');
        $mediaBack  = $itemable->getFirstMedia('back_templates');

        // One side only (front OR back OR none)
        if ($sides->count() === 1) {
            $side = $sides->first();
            return $this->downloadSingleSide($itemable, $side, $format, $mediaFront, $mediaBack);
        }

        // Two sides (front + back) => download as ZIP
        if ($sides->count() > 1) {
            return $this->downloadBothSidesAsZip($itemable, $format, $mediaFront, $mediaBack);
        }

        abort(404, 'No sides found for this template.');
    }

    protected function downloadSingleSide($template, $side, $format, $mediaFront, $mediaBack)
    {
        $side = $side === 'none' ? 'front' : $side;

        $media = $side === 'front' ? $mediaFront : $mediaBack;

        if (! $media) {
            abort(404, "$side image not found.");
        }

        $conversion = "{$side}_{$format}";

        // Path to conversion
        $path = $media->getPath($conversion);

        if (! file_exists($path)) {
            abort(404, "$side {$format} conversion not found.");
        }

        $ext      = $format === 'jpg' ? 'jpeg' : $format;
        $filename = "template-{$template->id}-{$side}.{$ext}";

        return response()->download($path, $filename, [
            'Content-Type' => "image/{$ext}",
        ]);
    }

    protected function downloadBothSidesAsZip($template, $format, $mediaFront, $mediaBack)
    {
        if (! $mediaFront && ! $mediaBack) {
            abort(404, 'No images found for this template.');
        }

        $ext = $format === 'jpg' ? 'jpeg' : $format;

        $files = [];

        if ($mediaFront) {
            $convFront = "front_{$format}";
            $pathFront = $mediaFront->getPath($convFront);

            if (! file_exists($pathFront)) {
                abort(404, 'Front image conversion not found.');
            }

            $files[$pathFront] = "template-{$template->id}-front.{$ext}";
        }

        if ($mediaBack) {
            $convBack = "back_{$format}";
            $pathBack = $mediaBack->getPath($convBack);

            if (! file_exists($pathBack)) {
                abort(404, 'Back image conversion not found.');
            }

            $files[$pathBack] = "template-{$template->id}-back.{$ext}";
        }

        if (empty($files)) {
            abort(404, 'No valid images found for this template.');
        }

        $zipFileName = "template-{$template->id}-sides.zip";
        $zipPath     = storage_path("app/tmp/{$zipFileName}");

        if (! is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Cannot create ZIP file.');
        }

        foreach ($files as $filePath => $nameInZip) {
            $zip->addFile($filePath, $nameInZip);
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

}

<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Models\ProductSpecification;
use Illuminate\Support\Facades\Response;


class FixedSpecController extends Controller
{
    public function destroy(ProductSpecification $productSpecification)
    {
        $productSpecification->delete();
        return Response::api();
    }
}

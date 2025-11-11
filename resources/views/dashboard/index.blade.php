@extends('layouts/contentLayoutMaster')

@section('title', 'Dashboard Ecommerce')

@section('vendor-style')
{{-- vendor css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/charts/apexcharts.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/toastr.min.css')) }}">
@endsection
@section('page-style')
{{-- Page css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/pages/dashboard-ecommerce.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/charts/chart-apex.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-toastr.css')) }}">

@endsection

@php
$discountTypes = [
['label' => 'Percentage', 'color' => '#24B094', 'width' => \App\Models\DiscountCode::whereType(\App\Enums\DiscountCode\TypeEnum::PERCENTAGE)->count()],
['label' => 'Fixed', 'color' => '#4E2775', 'width' => \App\Models\DiscountCode::whereType(\App\Enums\DiscountCode\TypeEnum::FIXED)->count()],
//['label' => 'Category', 'color' => '#F8AB1B', 'width' => '55%'],
//['label' => 'Product', 'color' => '#222245', 'width' => '35%'],
];
@endphp


@section('content')
<div class="card p-2">
    {{-- remove any @dd() before rendering --}}
    {{-- @dd($bestMonths) --}}

    <div class="row">
        <!-- order Chart Card -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border-2">
                <div class="card-header flex-column gap-1 align-items-start pb-0">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color:#FCF8FC;width:40px;height:40px;">
                            <i data-feather="file-text" class="font-medium-5" style="color:#24B094;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Total Orders</p>
                    </div>

                    <h2 class="text-black">
          <span class="fw-bolder text-black">
            {{ number_format((int) data_get($bestMonths, 'orders.value', 0)) }}
          </span>
                    </h2>

                    <h2 class="fs-5" style="color:#30A84D;">
                        <span><i data-feather="trending-up" class="font-medium-5"></i></span>
                        Highest Month
                        <span class="text-black ms-50">{{ data_get($bestMonths, 'orders.label', '—') }}</span>
                    </h2>
                </div>
                <div id="order-chart"></div>
            </div>
        </div>

        <!-- Earnings Chart Card -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border-2">
                <div class="card-header flex-column gap-1 align-items-start pb-0">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color:#FCF8FC;width:40px;height:40px;">
                            <i data-feather="tag" class="font-medium-5" style="color:#4E2775;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Earnings</p>
                    </div>

                    <h2 class="text-black">
          <span class="fw-bolder text-black">
            {{ number_format((float) data_get($bestMonths, 'revenue.value', 0)) }}
          </span> EGP
                    </h2>

                    <h2 class="fs-5" style="color:#30A84D;">
                        <span><i data-feather="trending-up" class="font-medium-5"></i></span>
                        Highest Month
                        <span class="text-black ms-50">{{ data_get($bestMonths, 'revenue.label', '—') }}</span>
                    </h2>
                </div>
                <div id="gained-chart"></div>
            </div>
        </div>

        <!-- Visits Chart Card -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border-2">
                <div class="card-header flex-column gap-1 align-items-start pb-0">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color:#FCF8FC;width:40px;height:40px;">
                            <i data-feather="eye" class="font-medium-5" style="color:#F8AB1B;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Visits</p>
                    </div>

                    <h2 class="text-black">
          <span class="fw-bolder text-black">
            {{ number_format((int) data_get($bestMonths, 'visits.value', 0)) }}
          </span>
                    </h2>

                    <h2 class="fs-5" style="color:#30A84D;">
                        <span><i data-feather="trending-up" class="font-medium-5"></i></span>
                        Highest Month
                        <span class="text-black ms-50">{{ data_get($bestMonths, 'visits.label', '—') }}</span>
                    </h2>
                </div>
                <div id="visits-chart"></div>
            </div>
        </div>

        <!-- Refunded Chart Card -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border-2">
                <div class="card-header flex-column gap-1 align-items-start pb-0">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color:#FCF8FC;width:40px;height:40px;">
                            <i data-feather="rotate-ccw" class="font-medium-5" style="color:#222245;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Refunded</p>
                    </div>

                    <h2 class="text-black">
          <span class="fw-bolder text-black">
            {{ number_format((int) data_get($bestMonths, 'orders_refunded.value', data_get($bestMonths, 'orders_refunded', 0))) }}
          </span>
                    </h2>

                    <h2 class="fs-5" style="color:#30A84D;">
                        <span><i data-feather="trending-up" class="font-medium-5"></i></span>
                        Highest Month
                        <span class="text-black ms-50">{{ data_get($bestMonths, 'orders_refunded.label', '—') }}</span>
                    </h2>
                </div>
                <div id="refund-chart"></div>
            </div>
        </div>
    </div>

    {{-- Products Card --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card border-2 p-2">
                <div class="d-flex flex-column flex-lg-row justify-content-between mb-50">
                    <p class="fs-2 text-black">Products</p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
        <span class="fs-6 text-black">
          <span class="fs-2 fw-bold">
            {{ number_format((int) data_get($bestMonths, 'categories.value', 0)) }}
          </span> Products
        </span>
                    <div class="progress progress-bar-primary w-75 me-1" style="height:6px">
                        <div class="progress-bar" role="progressbar"
                             aria-valuenow="{{ (int) data_get($bestMonths, 'categories.value', 0) }}"
                             aria-valuemin="0" aria-valuemax="100"
                             style="width: {{ (int) data_get($bestMonths, 'categories.value', 0) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Templates Card --}}
        <div class="col-md-6">
            <div class="card border-2 p-2">
                <div class="d-flex flex-column flex-lg-row justify-content-between mb-50">
                    <p class="fs-2 text-black">Templates</p>
                    <div class="d-flex align-items-center gap-1 gap-lg-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="rounded-1 me-1" style="width:24px;height:6px;background-color:#24B094"></span>
                                <span class="me-auto">Published</span>
                            </div>
                            <span>{{ number_format((int) data_get($bestMonths, 'published_templates', 0)) }} Templates</span>
                        </div>

                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <span class="rounded-1 me-1" style="width:24px;height:6px;background-color:#B3E3D8"></span>
                                <span class="me-auto">Draft</span>
                            </div>
                            <span>{{ number_format((int) data_get($bestMonths, 'draft_templates', 0)) }} Templates</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
        <span class="fs-6 text-black">
          <span class="fs-2 fw-bold">
            {{ number_format((int) data_get($bestMonths, 'templates', 0)) }}
          </span> Templates
        </span>
                    <div class="progress progress-bar-primary w-75 me-1" style="height:6px">
                        <div class="progress-bar" role="progressbar"
                             aria-valuenow="{{ (int) data_get($bestMonths, 'templates', 0) }}"
                             aria-valuemin="0" aria-valuemax="100"
                             style="width: {{ (int) data_get($bestMonths, 'templates', 0) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Products Card --}}
        <div class="col-md-6">
            <div class="card border-2 p-2">
                <div class="d-flex flex-column flex-lg-row justify-content-between mb-50">
                    <p class="fs-2 text-black">categories</p>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-6 text-black "><span class="fs-2 fw-bold ">{{(int) data_get($bestMonths, 'products.value', 0)}}</span> Products</span>
                    <div class="progress progress-bar-primary w-75 me-1" style="height: 6px">
                        <div class="progress-bar" role="progressbar" aria-valuenow="{{(int) data_get($bestMonths, 'products.value', 0)}}" aria-valuemin="0"
                            aria-valuemax="100" style="width: {{(int) data_get($bestMonths, 'categories.value', 0)}}%"></div>
                    </div>
                </div>
            </div>
        </div>



        {{-- Templates Card --}}
        <div class="col-md-6">
            <div class="card border-2 p-2">
                <div class="d-flex flex-column flex-lg-row justify-content-between mb-50">
                    <p class="fs-2 text-black">Templates</p>
                    <div class="d-flex align-items-center gap-1 gap-lg-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="d-flex align-items-center ">
                                <span class=" rounded-1 me-1"
                                    style="width: 24px; height: 6px;background-color:#24B094"></span>
                                <span class="me-auto">Published</span>
                            </div>
                            <span class="">{{ (int) data_get($bestMonths, 'published_templates.value', 0) }}Templates</span>
                        </div>

                        <div class="d-flex flex-column ">
                            <div class="d-flex align-items-center ">
                                <span class=" rounded-1 me-1"
                                    style="width: 24px; height: 6px;background-color:#B3E3D8"></span>
                                <span class="me-auto">Draft</span>
                            </div>
                            <span class="">{{ (int) data_get($bestMonths, 'draft_templates.value', 0) }} Templates</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-6 text-black "><span class="fs-2 fw-bold ">{{ (int) data_get($bestMonths, 'templates.value', 0) }}</span> Templates</span>
                    <div class="progress progress-bar-primary w-75 me-1" style="height: 6px">
                        <div class="progress-bar" role="progressbar" aria-valuenow="{{ (int) data_get($bestMonths, 'templates.value', 0) }}" aria-valuemin="0"
                            aria-valuemax="100" style="width: {{(int) data_get($bestMonths, 'templates.value', 0)}}%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <!-- Vists and Sales chart -->
        <div class="col-md-12 col-lg-8">
            <div class="d-flex justify-content-end">
                <select id="yearSelect" class="form-select w-auto mb-1 ">
                    {{-- Years will be inserted by JavaScript --}}
                </select>
            </div>
            <div id="apexColumnChart"></div>
        </div>
        <!-- Vists and Sales chart end -->

        <!-- Line Area Chart Card -->
        <div class="col-lg-4 col-12">
            <div class="card border-2">
                <div class="card-header flex-column align-items-start pb-0">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50"
                            style="background-color: #FCF8FC; width: 40px; height: 40px;">
                            <i data-feather="tag" class="font-medium-5" style="color: #4E2775;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Sales</p>
                    </div>
                    <div class="ps-3 w-100">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h2 class=" text-black "><span class="fw-bolder text-black ">20k</span> EGP</h2>
                            <span>in 2025</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                            <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up"
                                        class="font-medium-5"></i></span> Highest Month</h2>
                            <span class="text-black">December</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center w-100 ">
                            <h2 class="  fs-5" style="color: #E74943;"><span> <i data-feather="trending-down"
                                        class="font-medium-5"></i></span> Lowest Month</h2>
                            <span class="text-black">March</span>
                        </div>
                    </div>
                </div>
                <hr class="mx-2" />
                <div class="card-header flex-column align-items-start pb-0 mt-0 pt-0">
                    <div class="d-flex gap-1 align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50"
                            style="background-color:#FFFEF3; width: 40px; height: 40px;">
                            <i data-feather="eye" class="font-medium-5" style="color: #F8AB1B;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Visits</p>
                    </div>
                    <div class="ps-3 w-100">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h2 class=" text-black "><span class="fw-bolder text-black ">20k</span> visits</h2>
                            <span>in 2025</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                            <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up"
                                        class="font-medium-5"></i></span> Highest Month</h2>
                            <span class="text-black">December</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                            <h2 class="  fs-5" style="color: #E74943;"><span> <i data-feather="trending-down"
                                        class="font-medium-5"></i></span> Lowest Month</h2>
                            <span class="text-black">March</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Line Area Chart Card -->
    </div>

{{--    <div class="row d-flex gap-1 gap-md-0 align-items-stretch mb-2">--}}
{{--        <!-- Popular Products -->--}}
{{--        <div class="col-md-6">--}}
{{--            <div class="card border p-1 h-100">--}}
{{--                <h2 class="fs-4 text-black">Popular Products</h2>--}}
{{--                <!-- Product Card -->--}}
{{--                <div class="d-flex justify-content-between align-items-center">--}}
{{--                    <!-- Left: Product Info -->--}}
{{--                    <div class="d-flex align-items-center">--}}
{{--                        <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Product Image" class="mx-1 rounded"--}}
{{--                            style="width: 56px; height: 56px; object-fit: cover;">--}}
{{--                        <div class="d-flex flex-column">--}}
{{--                            <span class="fs-14 text-black">Product Name</span>--}}
{{--                            <span class="">In Stock: 25</span>--}}
{{--                            <span class="">Added: 2025-05-27</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <!-- Right: Sold Info -->--}}
{{--                    <div class="d-flex align-items-center">--}}
{{--                        <span class="fw-bold">Sold: 500</span>--}}
{{--                        <img src="{{ asset('images/success.svg') }}" alt="Product Image" class=" rounded"--}}
{{--                            style="width: 20px; height: 20px; object-fit: cover;">--}}

{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <!-- Popular Templates -->--}}
{{--        <div class="col-md-6">--}}
{{--            <div class="card border p-1 h-100">--}}
{{--                <h2 class="fs-4 text-black">Popular Templates</h2>--}}
{{--                <!-- Templates Card -->--}}
{{--                <div class="d-flex justify-content-between align-items-center">--}}
{{--                    <!-- Left: Templates Info -->--}}
{{--                    <div class="d-flex align-items-center">--}}
{{--                        <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Product Image" class="mx-1 rounded"--}}
{{--                            style="width: 56px; height: 56px; object-fit: cover;">--}}
{{--                        <div>--}}
{{--                            <p class="mb-25 fw-bold">Log Template</p>--}}
{{--                            <p class="mb-0 ">Added: 2025-05-27</p>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <!-- Right: Sold Info -->--}}
{{--                    <div class="d-flex align-items-center">--}}
{{--                        <span class="fw-bold mx-1">Customized: 500</span>--}}
{{--                        <img src="{{ asset('images/success.svg') }}" alt="Product Image" class=" rounded"--}}
{{--                            style="width: 20px; height: 20px; object-fit: cover;">--}}

{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    <div class="row d-flex gap-1 gap-md-0 align-items-stretch">
        <!-- Discount Codes Chart -->
        <div class="col-md-6">
            <div class="card border p-2 h-100">
                <div class="d-flex justify-content-between align-items-center mb-50">
                    <p class="fs-2 text-black">Discount Codes</p>
                </div>

                <div class="d-flex justify-content-between mb-2 flex-wrap">
                    @foreach ($discountTypes as $type)
                    <div class="d-flex flex-column align-items-center me-2 mb-1">
                        <div class="d-flex align-items-center">
                            <span class="rounded-1 me-1"
                                style="width: 24px; height: 6px; background-color: {{ $type['color'] }}"></span>
                            <span>{{ $type['label'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                @foreach ($discountTypes as $type)
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="w-75">
                        <div class="progress" style="height: 6px; width: {{ $type['width'] }}">
                            <div class="progress-bar" role="progressbar"
                                style="width: 100%; background-color: {{ $type['color'] }}"></div>
                        </div>
                    </div>
                    <span class="text-black" style="font-size: 12px;"><span>{{ $type['width'] }}</span> Discount Code</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="col-md-6">
            <div class="card border h-100">
                <div class="card-header flex-column align-items-start pb-0">
                    <div class="d-flex align-items-center mb-1">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50"
                            style="background-color: #FCF8FC; width: 40px; height: 40px;">
                            <i data-feather="tag" class="font-medium-5" style="color: #4E2775;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0 ms-2">Revenue</p>
                    </div>

                    <div class="ps-3 w-100">
                        <p>Total earnings from orders used discount codes.</p>

                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h2 class="text-black"><span class="fw-bolder"></span> EGP</h2>
                            <span>in 2025</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center w-100 mt-1">
                            <h2 class="fs-5 text-success">
                                <i data-feather="trending-up" class="font-medium-5"></i> Highest Month
                            </h2>
                            <span class="text-black">December</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h2 class="fs-5 text-danger">
                                <i data-feather="trending-down" class="font-medium-5"></i> Lowest Month
                            </h2>
                            <span class="text-black">March</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>




@endsection

@section('vendor-script')
{{-- vendor files --}}
<script src="{{ asset(mix('vendors/js/charts/apexcharts.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>
@endsection
@section('page-script')
{{-- Page js files --}}
<script src="{{ asset(mix('js/scripts/pages/dashboard-ecommerce.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/dashboard-analytics.js')) }}"></script>
<!-- Vists and Sales chart -->
<script>
    handleAjaxFormSubmit("#editMockupForm", {
        successMessage: "Mockup Updated Successfully",
        onSuccess: function() {
            $('#editMockupModal').modal('hide');
            location.reload();
        }
    })
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.getElementById('yearSelect');
        const currentYear = new Date().getFullYear();
        const startYear = 2015;

        for (let year = currentYear; year >= startYear; year--) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearSelect.appendChild(option);
        }

        // Default chart load
        loadChart(currentYear);

        // Change chart on year change
        yearSelect.addEventListener('change', function() {
            const selectedYear = this.value;
            loadChart(selectedYear);
        });

        function loadChart(year) {
            const options = {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Visits',
                    data: [120, 200, 150, 80, 70, 110, 130, 160, 90, 100, 140, 180]
                }, {
                    name: 'Sales',
                    data: [60, 100, 80, 40, 30, 60, 70, 90, 50, 70, 100, 120]
                }],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                },
                colors: ['#4EBFA7', '#B673BD'],

                plotOptions: {
                    bar: {
                        columnWidth: '25%',
                        borderRadius: 4
                    }
                },
                legend: {
                    position: 'top'
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    strokeDashArray: 4
                }
            };

            const chartDiv = document.querySelector("#apexColumnChart");
            chartDiv.innerHTML = ""; // Clear old chart
            const chart = new ApexCharts(chartDiv, options);
            chart.render();
        }
    });
</script>
<!-- Vists and Sales chart end -->
@endsection

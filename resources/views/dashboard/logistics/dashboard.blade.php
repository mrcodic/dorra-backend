@extends('layouts/contentLayoutMaster')

@section('title', 'Logistics')
@section('main-page', 'Logistics')

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
['label' => 'Percentage', 'color' => '#24B094', 'width' => '85%'],
['label' => 'Fixed', 'color' => '#4E2775', 'width' => '75%'],
['label' => 'Category', 'color' => '#F8AB1B', 'width' => '55%'],
['label' => 'Product', 'color' => '#222245', 'width' => '35%'],
];
@endphp

@section('content')
<div class="card p-2">
    <div class="row">
        <!-- order Chart Card starts -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border border-2">
                <div class="card-header flex-column align-items-start pb-0">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color: #FCF8FC; width: 40px; height: 40px;">
                            <i data-feather="file-text" class="font-medium-5" style="color: #24B094;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Total Orders</p>
                    </div>
                    <h2 class=" text-black "><span class="fw-bolder text-black ">20k</span> EGP</h2>
                    <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up" class="font-medium-5"></i></span> Highest Month</h2>
                </div>
           
            </div>
        </div>
        <!-- order Chart Card ends -->
        <!-- Earnings Chart Card starts -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border border-2">
                <div class="card-header flex-column align-items-start pb-0">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color: #FCF8FC; width: 40px; height: 40px;">
                            <i data-feather="tag" class="font-medium-5" style="color: #4E2775;"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Earnings</p>
                    </div>
                    <h2 class=" text-black "><span class="fw-bolder text-black ">20k</span> EGP</h2>
                    <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up" class="font-medium-5"></i></span> Highest Month</h2>
                </div>
              
            </div>
        </div>
        <!-- Earnings Chart Card ends -->
        <!-- Visits Chart Card starts -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border border-2">
                <div class="card-header flex-column align-items-start pb-0">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color: #FCF8FC; width: 40px; height: 40px;">
                            <i data-feather="eye" class="font-medium-5" style="color: #F8AB1B"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Visits</p>
                    </div>
                    <h2 class=" text-black "><span class="fw-bolder text-black ">20k</span> EGP</h2>
                    <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up" class="font-medium-5"></i></span> Highest Month</h2>
                </div>
               
            </div>
        </div>
        <!-- Visits Chart Card ends -->

        <!-- Refunded Chart Card starts -->
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="card border border-2">
                <div class="card-header flex-column align-items-start pb-0">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color: #FCF8FC; width: 40px; height: 40px;">
                            <i data-feather="rotate-ccw" class="font-medium-5" style="color: #222245"></i>
                        </div>
                        <p class="text-black fs-4 mb-0">Refunded</p>
                    </div>
                    <h2 class=" text-black "><span class="fw-bolder text-black ">20k</span> EGP</h2>
                    <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up" class="font-medium-5"></i></span> Highest Month</h2>
                </div>
            </div>
        </div>
        <!-- Refunded Chart Card ends -->
    </div>
    <div class="row">
        <!-- Vists and Sales chart -->
        <div class="col-md-8">
            <div class="d-flex justify-content-end">
                <select id="yearSelect" class="form-select w-auto mb-1 ">
                    {{-- Years will be inserted by JavaScript --}}
                </select>
            </div>
            <div id="apexColumnChart"></div>
        </div>
        <!-- Vists and Sales chart end -->

        <!-- Line Area Chart Card -->
        <div class="col-lg-4 col-sm-6 col-12 ">
            <div class="card border border-2">
                <div class="card-header flex-column align-items-start pb-0">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color: #FCF8FC; width: 40px; height: 40px;">
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
                            <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up" class="font-medium-5"></i></span> Highest Month</h2>
                            <span class="text-black">December</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center w-100 ">
                            <h2 class="  fs-5" style="color: #E74943;"><span> <i data-feather="trending-down" class="font-medium-5"></i></span> Lowest Month</h2>
                            <span class="text-black">March</span>
                        </div>
                    </div>
                </div>
                <hr class="mx-2" />
                <div class="card-header flex-column align-items-start pb-0 mt-0 pt-0">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-center rounded-3 p-50" style="background-color:#FFFEF3; width: 40px; height: 40px;">
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
                            <h2 class="  fs-5" style="color: #30A84D;"><span> <i data-feather="trending-up" class="font-medium-5"></i></span> Highest Month</h2>
                            <span class="text-black">December</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                            <h2 class="  fs-5" style="color: #E74943;"><span> <i data-feather="trending-down" class="font-medium-5"></i></span> Lowest Month</h2>
                            <span class="text-black">March</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Line Area Chart Card -->
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
                    name: 'Success',
                    data: [120, 200, 150, 80, 70, 110, 130, 160, 90, 100, 140, 180]
                }, {
                    name: 'Fail',
                    data: [60, 100, 80, 40, 30, 60, 70, 90, 50, 70, 100, 120]
                }],
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                },
                colors: ['#30A84D', '#E74943'],

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
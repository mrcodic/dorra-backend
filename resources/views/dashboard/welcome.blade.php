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



@section('content')


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

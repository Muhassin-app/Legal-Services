$(document).ready(function () {
    
    const $flatpickr = $("#range-datepicker").flatpickr({
        mode: "range",
        dateFormat: "d M Y",
        onClose: function (selectedDates, dateStr, instance) {
            getDashboardData(dashboard_filter_url);
        }
    });
    $("#app_managers").on('change',function () {
        getDashboardData(dashboard_filter_url);
    });
    
    $("#reportType").on('change',function () {
        getDashboardData(dashboard_filter_url);
    });

    $("#dashboard_refresh_btn").click(function () {
        window.location.reload();
    });
    getDashboardData(dashboard_filter_url);

    function getDashboardData(dashboard_filter_url) {
        var date_filter = $('#range-datepicker').val();
        var manager_id = $('#app_managers').val();
        var reportType = $('#reportType').val();

        $.getJSON(dashboard_filter_url, { manager_id : manager_id ,date_filter: date_filter,reportType:reportType}, function (response) {
            if (response.status == 'Success') {
                //$('#range-datepicker').val(response.data.setWeekDate);
                $('#total_products').html('+ ' + response.data.total_products);
                $('#notification_counts').html(response.data.orderNotificationCnt);
                $('#total_revenue').html(response.data.currencySymbol + response.data.total_revenue);
                $('#total_sold_products').html(response.data.total_sold_products);
                $('#total_customers').html(response.data.total_customers);
                $('#total_orders').html(response.data.total_orders);
                $('#total_vendors').html(response.data.total_vendors);
                $('#total_managers').html(response.data.managersCount);
                $('#revenueCurrentWeek').html(response.data.currencySymbol + response.data.revenueCurrentWeek);
                $('#revenueLastWeek').html(response.data.currencySymbol + response.data.revenueLastWeek);
                if (response.data.customers_increase != '') {
                    $('#customers_change').html('');
                    $('#customers_change').append('<span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i>' + response.data.customers_increase + '%</span><span class="text-nowrap">Since last month</span>');
                } else if (response.data.customers_decrease != '') {
                    $('#customers_change').html("");
                    $('#customers_change').append('<span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i>' + response.data.customers_decrease + '%</span><span class="text-nowrap">Since last month</span>');
                } else {
                    $('#customers_change').html("");
                    $('#customers_change').append('<span class="text-danger me-2">0%</span><span class="text-nowrap">Since last month</span>');
                }
                if (response.data.orders_increase != '') {
                    $('#orders_change').html('');
                    $('#orders_change').append('<span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i>' + response.data.orders_increase + '%</span><span class="text-nowrap">Since last month</span>');
                } else if (response.data.orders_decrease != '') {
                    $('#orders_change').html('');
                    $('#orders_change').append('<span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i>' + response.data.orders_decrease + '%</span><span class="text-nowrap">Since last month</span>');
                } else {
                    $('#orders_change').html('');
                    $('#orders_change').append('<span class="text-danger me-2">0%</span><span class="text-nowrap">Since last month</span>');
                }
                if (response.data.revenue_increase != '') {
                    $('#revenue_change').html('');
                    $('#revenue_change').append('<span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i>' + response.data.revenue_increase + '%</span><span class="text-nowrap">Since last month</span>');
                } else if (response.data.revenue_decrease != '') {
                    $('#revenue_change').html('');
                    $('#revenue_change').append('<span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i>' + response.data.revenue_decrease + '%</span><span class="text-nowrap">Since last month</span>');
                } else {
                    $('#revenue_change').html('');
                    $('#revenue_change').append('<span class="text-danger me-2">0%</span><span class="text-nowrap">Since last month</span>');
                }
                if (response.data.products_increase != '') {
                    $('#products_change').html('');
                    $('#products_change').append('<span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i>' + response.data.products_increase + '%</span><span class="text-nowrap">Since last month</span>');
                } else if (response.data.products_decrease != '') {
                    $('#products_change').html('');
                    $('#products_change').append('<span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i>' + response.data.products_decrease + '%</span><span class="text-nowrap">Since last month</span>');
                } else {
                    $('#products_change').html('');
                    $('#products_change').append('<span class="text-danger me-2">0%</span><span class="text-nowrap">Since last month</span>');
                }
                Worldmap(response.data.markers);
                updateRevenue(response.data.monthwise_revenue, response.data.currencySymbol);
                updateRevenueLineChart(response.data.currentweek_revenue_daywise, response.data.previousweek_revenue_daywise, response.data.currencySymbol);
                // if (response.data.locationwise_revenue) {
                //     $('#revenue_locations').html('');
                //     if (response.data.locationwise_revenue != '') {
                //         response.data.locationwise_revenue.forEach(el => {
                //             var sum = Math.round(el.sum);
                //             var orderCount = response.data.currentyear_ordercount;
                //             var percent = Math.round((el.addressCount / orderCount) * 100);
                //             $('#revenue_locations').append('<h5 class="mb-1 mt-0 fw-normal">' + el.address.city + '</h5><div class="progress-w-percent"><span class="progress-value fw-bold">' + response.data.currencySymbol + sum + "</span><div class='progress progress-sm'><div class='progress-bar' role='progressbar' style='width:" + percent + "%;' aria-valuenow='72' aria-valuemin='0' aria-valuemax='100'></div></div></div>");
                //         });
                //     } else {
                //         $('#revenue_locations').append('<h5 class="mb-1 mt-0 fw-normal text-center">No data found</h5>');
                //     }
                // }
                if (response.data.locationwise_revenue) {
                    var sum = 0;
                    var orderCount = 0;
                    var percent = 0;
                    var city = '';
                    var addressCount =0;
                    $('#revenue_locations').html('');
                    if (response.data.locationwise_revenue != '') {
                        var html = '';                        
                        var arr = response.data.locationwise_revenue;
                        for (const property in arr) {
                            console.log(arr[property]);
                            var data = arr[property];
                            var  orderCount = response.data.currentyear_ordercount;
                            var percent = Math.round((data.addressCount / orderCount) * 100);
                            var html = '<span id="'+data.city+'"><h5 class="mb-1 mt-0 fw-normal">' + data.city  + '</h5><div class="progress-w-percent"><span class="progress-value fw-bold">' + Math.round( data.sum)+ "</span><div class='progress progress-sm'><div class='progress-bar' role='progressbar' style='width:" + percent + "%;' aria-valuenow='72' aria-valuemin='0' aria-valuemax='100'></div></div></div></span>";
                            $('#revenue_locations').append(html);
                
                          }
                    } else {
                        $('#revenue_locations').append('<h5 class="mb-1 mt-0 fw-normal text-center">No data found</h5>');
                    }
                }
            }
        });
    }
    function Worldmap(markers) {
        $('#world-map').html("");
        var a = ["#43bee1"],
            e = $("#world-map").data("colors");
        $("#world-map").vectorMap({
            hoverColor: !1,
            hoverOpacity: 0.7,
            shape: 'square',
            map: "world_mill_en",
            backgroundColor: "transparent",
            normalizeFunction: "polynomial",
            regionStyle: { initial: { fill: "#ced4da" } },
            markerStyle: { initial: { r: 9, fill: a[0], "fill-opacity": 0.9, stroke: "#fff", "stroke-width": 7, "stroke-opacity": 0.4 }, hover: { stroke: "#fff", "fill-opacity": 1, "stroke-width": 1.5 } },
            markers: markers,
        });
    }

    // New revenue bar chart monthly data show
    function updateRevenue(newrevenue, currency) {
        $('#revenue-bar-chart').html("");
        var colors = ['#43bee1', '#e3eaef'];
        var dataColors = $("#revenue-bar-chart").data('colors');
        if (dataColors) {
            colors = dataColors.split(",");
        }
        var options = {
            chart: {
                height: 364,
                type: 'bar',
                stacked: !0
            },
            plotOptions: {
                bar: {
                    horizontal: !1, columnWidth: "20%"
                }
            },
            dataLabels: { enabled: !1 },
            stroke: { show: !0, width: 2, colors: ["transparent"] },
            series: [{ name: "Revenue", data: newrevenue }],
            zoom: { enabled: !1 },
            legend: { show: !1 },
            colors: colors,
            xaxis: { categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], axisBorder: { show: !1 } },
            yaxis: { labels: { formatter: function (e) { return currency + e }, offsetX: -15 } },
            fill: { opacity: 1 },
            tooltip: { y: { formatter: function (e) { return currency + e } } },
        };
        var chart = new ApexCharts(document.querySelector("#revenue-bar-chart"), options);
        chart.render();
    }

    // New line chart current and previous week data show
    function updateRevenueLineChart(current_data, previous_data, currency) {
        $('#revenue-line-chart').html("");
        var colors = ["#43bee1", "#0acf97", "#fa5c7c", "#ffbc00"];
        var dataColors = $("#revenue-line-chart").data('colors');
        if (dataColors) {
            colors = dataColors.split(",");
        }
        var options = {
            chart: { height: 364, type: "line", dropShadow: { enabled: !0, opacity: .2, blur: 7, left: -7, top: 7 } },
            dataLabels: { enabled: !1 },
            stroke: { curve: "smooth", width: 4 },
            series: [{ name: "Current Week", data: current_data }, { name: "Previous Week", data: previous_data }],
            colors: colors,
            zoom: { enabled: !1 },
            legend: { show: !1 },
            xaxis: {
                type: "string", categories: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                tooltip: { enabled: !1 },
                axisBorder: { show: !1 }
            },
            yaxis: { labels: { formatter: function (e) { return currency + e }, offsetX: -15 } },
        };
        var chart1 = new ApexCharts(document.querySelector("#revenue-line-chart"), options);
        chart1.render();
    }
});

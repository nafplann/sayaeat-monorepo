const Dashboard = {
    index() {
        document.querySelectorAll('.quick-overview').forEach(item => {
            Dashboard.getData(item);
        });
    },

    getData(item) {
        let model = item.dataset.model;
        let chart = item.dataset.chart;

        Utils.ajax('/manage/dashboard/get-overview', 'GET', 'json', {model})
            .then(results => {
                item.querySelector('.count').innerHTML = results.count;
                item.querySelector('.history').innerHTML = results.history;

                let bar = {
                    type: 'bar',
                    height: 42,
                    barWidth: 4,
                    barColor: '#fff',
                    barSpacing: 2
                };

                let line = {
                    type: 'line',
                    width: 72,//73
                    height: 42,
                    lineColor: '#fff',
                    fillColor: 'rgba(0,0,0,0)',
                    lineWidth: 1.25,
                    maxSpotColor: 'rgba(255,255,255,0.4)',
                    minSpotColor: 'rgba(255,255,255,0.4)',
                    spotColor: 'rgba(255,255,255,0.4)',
                    spotRadius: 3,
                    highlightSpotColor: '#fff',
                    highlightLineColor: 'rgba(255,255,255,0.4)'
                };
                $(item).find('.sparkline-bar-stats').sparkline('html', chart === 'bar' ? bar : line);
            });
    },

    renderDriverChart(start, end) {
        Utils.ajax(Utils.baseUrl('driver-daily-report/driver-rank'), 'GET', 'application/json', {
            start: start.format('YYYY-MM-DD'),
            end: end.format('YYYY-MM-DD')
        }, {
            success: function (response) {
                if (!response.length) {
                    return;
                }

                if (window.driverOrders) {
                    window.driverOrders.destroy();
                }

                window.driverOrders = new Chart(document.getElementById('driver-orders'), {
                    type: 'bar',
                    data: {
                        labels: response.map(i => i.label),
                        datasets: [
                            {
                                label: 'Orders',
                                data: response.map(i => i.total_orders),
                                borderWidth: 1,
                                backgroundColor: 'rgba(50,199,135,0.5)',
                                borderColor: 'rgba(50,199,135,1)',
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                }
                            }
                        },
                        responsive: true,
                    }
                });

                if (window.driverRevenue) {
                    window.driverRevenue.destroy();
                }

                window.driverRevenue = new Chart(document.getElementById('driver-revenue'), {
                    type: 'bar',
                    data: {
                        labels: response.map(i => i.label),
                        datasets: [
                            {
                                label: 'Revenue',
                                data: response.map(i => i.revenue),
                                borderWidth: 1,
                                backgroundColor: 'rgba(33,150,243,0.5)',
                                borderColor: 'rgba(33,150,243,1)',
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        responsive: true,
                    }
                });
            },
            error: function (error) {
                console.log(error);
            }
        });
    },

    renderDailyRevenueChart(start, end) {
        let revenue = $('#revenue');

        let numberFormatter = new Intl.NumberFormat('id-ID', {
            style: 'decimal',
            currency: 'IDR',
            minimumFractionDigits: 0,
        });

        revenue.text(0);

        Utils.ajax(Utils.baseUrl('manage/dashboard/daily-revenue'), 'GET', 'application/json', {
            start: start.format('YYYY-MM-DD'),
            end: end.format('YYYY-MM-DD')
        }, {
            success: function (response) {
                if (!response.length) {
                    return;
                }

                if (window.deliveryFeesRevenue) {
                    window.deliveryFeesRevenue.destroy();
                }

                let totalRevenue = response.reduce((acc, item) => {
                    return acc + item.delivery_fees + item.service_fees + item.items_profit;
                }, 0);

                revenue.text(numberFormatter.format(totalRevenue));

                window.deliveryFeesRevenue = new Chart(document.getElementById('delivery-fees-revenue'), {
                    type: 'bar',
                    data: {
                        labels: response.map(i => i.label),
                        datasets: [
                            {
                                label: 'Delivery Fees',
                                data: response.map(i => i.delivery_fees),
                                borderWidth: 1,
                                backgroundColor: 'rgba(50,199,135,0.5)',
                                borderColor: 'rgba(50,199,135,1)',
                            },
                            {
                                label: 'Service Fees',
                                data: response.map(i => i.service_fees),
                                borderWidth: 1,
                                backgroundColor: 'rgba(33,150,243,0.5)',
                                borderColor: 'rgba(33,150,243,1)',
                            },
                            {
                                label: 'Menu Items Profit',
                                data: response.map(i => i.items_profit),
                                borderWidth: 1,
                                backgroundColor: 'rgba(255,107,104,0.5)',
                                borderColor: 'rgba(255,107,104,1)',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                            },
                            y: {
                                stacked: true
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    footer: (tooltipItems) => {
                                        let sum = 0;

                                        tooltipItems.forEach(function (tooltipItem) {
                                            sum += tooltipItem.parsed.y;
                                        });

                                        return 'Total: ' + numberFormatter.format(sum);
                                    },
                                }
                            }
                        }
                    }
                });

            },
            error: function (error) {
                console.log(error);
            }
        });
    },

    initRangePicker(onRangeChanged) {
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, onRangeChanged);
    },

    renderDriverIncome(start, end) {
        let revenue = $('#revenue');

        let numberFormatter = new Intl.NumberFormat('id-ID', {
            style: 'decimal',
            currency: 'IDR',
            minimumFractionDigits: 0,
        });

        revenue.text(0);

        Utils.ajax(Utils.baseUrl('driver-daily-report/driver-income'), 'GET', 'application/json', {
            start: start.format('YYYY-MM-DD'),
            end: end.format('YYYY-MM-DD')
        }, {
            success: function (response) {
                if (!response) return;

                let driverIncome = $('#driver-income').find('tbody');
                driverIncome.empty();

                Object.keys(response).forEach(key => {
                    let deposit = response[key].deposit > 0 ? -Math.abs(response[key].deposit) : 0;
                    let credit = response[key].credit;
                    let total = deposit + credit;

                    driverIncome.append(`
                        <tr>
                            <th scope="row">${response[key].driver.code}</th>
                            <td>${response[key].driver.name}</td>
                            <td>
                                <textarea class="order-data" hidden>${JSON.stringify(response[key].orders)}</textarea>
                                <a href="" class="order-details">${response[key].orders.length}</a>
                            </td>
                            <td>${numberFormatter.format(response[key].income)}</td>
                            <td>${numberFormatter.format(deposit)}</td>
                            <td>${numberFormatter.format(credit)}</td>
                            <td class="${total < 0 ? 'text-danger' : 'text-success'}">${numberFormatter.format(total)}</td>
                        </tr>
                    `);
                });
            },
            error: function (error) {
                console.log(error);
            }
        });
    },
}

export default Dashboard;

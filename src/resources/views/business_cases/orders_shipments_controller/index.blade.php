@extends('layouts.app')

@section('script')
    <script type="text/javascript">
        var orderItems = [];
        var mappedStores = {};
        var mappedShipmentStatuses = ['Not Shipped', 'Partially Shipped', 'Shipped'];
        var table;

        axios.post('{{ route('stores.list') }}')
            .then(response => {
                if (response.data !== undefined) {
                    stores = response.data.data;
                    $.unblockUI();

                    if (stores.length == 0) {
                        Swal.fire(
                            'Error!',
                            'Do not have store info, please check API log.',
                            'error'
                        );
                        $.unblockUI();
                        return;
                    }

                    $.each(stores, function (key, value) {
                        mappedStores[value.cart_id] = value;
                    });

                    if (response.data.log) {
                        for (let k = 0; k < response.data.log.length; k++) {
                            logItems.push(response.data.log[k]);
                        }

                        calculateLog();
                    }

                    setTimeout(() => {
                        stores.forEach((item, index) => {
                            setTimeout(() => {
                                loadData(item.store_key, item.cart_id, item.url);
                            }, index * 2000);
                        });
                    }, 2000);
                }
            })
            .catch(error => {
                Swal.fire('Error!', error, 'error');
                $.unblockUI();
            });

        function loadData(storeKey, cart_id, url) {
            $('#dtable .infoItem, #dtable .addShipment').prop('disabled', true);
            $('#_btnCheckNewOrder').prop('disabled', true);
            $('.ajax_info').empty().text('  Loading orders information for ' + url + '.');

            return axios({
                method: 'post',
                url: '{{route('orders.get-orders-with-shipments') }}' + '/' + storeKey,
                data: {
                    cart_id: cart_id,
                    length: 15,
                    start: 0,
                    limit: 15,
                    sort_by: 'create_at',
                    sort_direct: 'desc',
                }
            }).then(function (response) {
                let orders = response.data.data;
                let logs = response.data.log;

                $('.ajax_info').empty().text('  Adding orders.');

                $.each(orders, function (index, value) {
                    value.cart_id = mappedStores[value.cart_id];
                    orderItems.push(value);
                });

                orderItems.sort(function (val1, val2) {
                    var dateVal1 = new Date(val1.create_at.value);
                    var dateVal2 = new Date(val2.create_at.value);
                    return dateVal2 - dateVal1;
                });

                //update log count
                if (response.data.log) {
                    for (let k = 0; k < response.data.log.length; k++) {
                        logItems.push(response.data.log[k]);
                    }
                    calculateLog();
                }

                var datatable = $('#dtable').dataTable().api();

                datatable.clear();
                datatable.rows.add(orderItems);
                datatable.order([1, "desc"]).draw();

                $('.ajax_info').empty();
                $('#_btnCheckNewOrder').prop('disabled', false);
                $.unblockUI();

                $.growlUI('Notification', 'Order data loaded successfull!', 500);

                initFilters();
            }).catch(function (error) {
                $('.ajax_info').empty();
                $('#_btnCheckNewOrder').prop('disabled', false);
                $.unblockUI();

                Swal.fire(
                    'Error!',
                    'Do not have store info, please check API log.',
                    'error'
                )
            });
        }

        function initFilters() {
            var names = getUniqueName();
            var status = getUniqueStatus();
            var store = getUniqueStore();

            yadcf.init(table, [
                {
                    column_number: 2,
                    select_type: 'select2',
                    data: store,
                    select_type_options: {width: '200px'}
                },
                {
                    column_number: 3,
                    select_type: 'select2',
                    data: names,
                    select_type_options: {width: '150px'}
                },
                {
                    column_number: 5,
                    select_type: 'select2',
                    data: status,
                    select_type_options: {width: '150px'}
                },
                {
                    column_number: 6,
                    select_type: 'select2',
                    data: [
                        {value: 0, label: "Not Shipped"},
                        {value: 1, label: "Partially Shipped"},
                        {value: 2, label: "Shipped"}
                    ],
                    select_type_options: {width: '100px',}
                }
            ]);

        }

        function getUniqueName() {
            var uniqueItem = [];
            orderItems.filter(function (item) {
                let name = item.customer.first_name + ' ' + item.customer.last_name;
                if (!~uniqueItem.indexOf(name)) {
                    uniqueItem.push(name);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniqueStatus() {
            var uniqueItem = [];
            orderItems.filter(function (item) {
                if (!~uniqueItem.indexOf(item.status.name)) {
                    uniqueItem.push(item.status.name);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniqueStore() {
            var uniqueItem = [];
            orderItems.filter(function (item) {
                let url = (item.cart_id.url) ? item.cart_id.url : '';
                if (!~uniqueItem.indexOf(url)) {
                    uniqueItem.push(url);
                    return item;
                }
            });
            return uniqueItem;
        }

        function checkNewOrders() {
            setTimeout(() => {
                stores.forEach((item, index) => {
                    setTimeout(() => {
                        loadData(item.store_key, item.cart_id, item.url);
                    }, index * 2000);
                });

                $('#_btnCheckNewOrder').removeAttr('disabled');
            }, 2000);
        }

        function disableProducts(elem, e) {
            e.preventDefault();

            if ($(elem).prop('checked')) {
                $('#product_groups input').prop('disabled', true).prop('readonly', true);
            } else {
                $('#product_groups input').prop('disabled', false).prop('readonly', false);
            }
        }

        function addOrderShipment(storeKey, orderId, row) {
            let action = "{{ route('order.shipment.add', ['store_key' => ':storeKey', 'order_id' => ':orderId']) }}";
            action = action.replace(':storeKey', storeKey);
            action = action.replace(':orderId', orderId);

            axios.get(action)
                .then(function (response) {
                    Swal.fire({
                        title: 'Add new Shipment for order #' + orderId,
                        html: response.data.data,
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-danger'
                        },
                        showCancelButton: true,
                        showCloseButton: true,
                        buttonsStyling: false,
                        confirmButtonText: 'Create',
                        width: '70%',
                        allowOutsideClick: false,
                        preConfirm: (pconfirm) => {

                            $('.swal2-content').find('.is-invalid').removeClass('is-invalid');
                            $($(document.getElementById('_form_errors')).parent()).hide();
                            let shipmentStatus = $('.swal2-content form #shipment_status').val();
                            let fact = $('.swal2-content form')[0].action;
                            var formData = getFormData($('.swal2-content form'));

                            return axios.post(fact, formData, {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            }).then(function (presponse) {
                                if (presponse.data.success) {
                                    let rowData = table.row(row).data();
                                    rowData.shipment_status = presponse.data.shipment_status;
                                    table.row(row).data(rowData).draw(false, false);

                                    if (presponse.data.log) {
                                        for (let k = 0; k < presponse.data.log.length; k++) {
                                            logItems.push(presponse.data.log[k]);
                                        }

                                        calculateLog();

                                    }

                                    Swal.fire(
                                        'OK!',
                                        'New shipment created succesfully! ',
                                        'success'
                                    );
                                } else {
                                    Swal.fire(
                                        'ERROR!',
                                        presponse.data.errormessage,
                                        'error'
                                    );
                                }

                                return true;
                            }).catch(function (error) {
                                if (typeof error.response.data.errors != 'undefined') {

                                    $.each(error.response.data.errors, function (index, value) {
                                        if (typeof index !== 'undefined' || typeof value !== 'undefined') {
                                            let obj = $(document.getElementById(index));
                                            let err = $(obj).parent().parent().find('.invalid-feedback');
                                            $(err).empty().append(value.shift());
                                            $(obj).addClass('is-invalid')
                                        }
                                    });
                                }

                                return false;
                            });
                        },
                    });

                    //update log count
                    if (response.data.log) {
                        for (let k = 0; k < response.data.log.length; k++) {
                            logItems.push(response.data.log[k]);
                        }
                        calculateLog();

                    }
                    $.unblockUI();

                })
                .catch(function (error) {
                    $.unblockUI();

                    Swal.fire(
                        'Error!',
                        'Failed info ' + id,
                        'error'
                    )
                });
        }

        function updateShipment(elem, shipmentId, trackingId) {
            $(elem).empty().text('... Updating Tracking Number');
            $('.swal2-content').find('.is-invalid').removeClass('is-invalid');
            $($(document.getElementById('_form_errors')).parent()).hide();
            let form = $('#shimpent-' + shipmentId);
            let fact = form[0].action;
            var formData = getFormData(form);

            return axios.post(fact, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(function (presponse) {
                if (presponse.data.success) {
                    if (presponse.data.log) {
                        for (let k = 0; k < presponse.data.log.length; k++) {
                            logItems.push(presponse.data.log[k]);
                        }

                        calculateLog();

                    }

                    if (presponse.data.updatedItems !== undefined && presponse.data.updatedItems === 0) {
                        return true;
                    }

                    $('#alert-' + shipmentId).addClass('alert-info').removeClass('alert-danger').show().
                    find('#_form_errors').empty().text('Shipment id = \'' + shipmentId + '\' updated successfully!');
                } else {
                    if (typeof presponse.data.errormessage != 'undefined') {
                        $('#alert-' + shipmentId).removeClass('alert-info').addClass('alert-danger').show().
                        find('#_form_errors').empty().text(presponse.data.errormessage);
                    }
                }

                setTimeout(function () {
                    $('#alert-' + shipmentId)
                        .removeClass('alert-info')
                        .removeClass('alert-danger')
                        .hide()
                        .find('#_form_errors').empty()
                    },
                    3000
                );
                $(elem).empty().text('Update Tracking Number');

                return true;
            }).catch(function (error) {
                if (typeof error.response.data.errors != 'undefined') {

                    $.each(error.response.data.errors, function (index, value) {
                        if (typeof index !== 'undefined' || typeof value !== 'undefined') {
                            let obj = $(document.getElementById(index));
                            let err = $(obj).parent().parent().find('.invalid-feedback');
                            $(err).empty().append(value.shift());
                            $(obj).addClass('is-invalid')
                        }
                    });
                }

                $(elem).empty().text('Update Tracking Number');
                $('#alert-' + shipmentId).removeClass('alert-info').removeClass('alert-danger').hide().
                find('#_form_errors').empty();
                setTimeout(function () {
                        $('#alert-' + shipmentId)
                            .removeClass('alert-info')
                            .removeClass('alert-danger')
                            .hide()
                            .find('#_form_errors').empty()
                    },
                    3000
                );

                return false;
            });
        }

        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#_btnCheckNewOrder').prop('disabled', true);

            blockUiStyled('<h4>Loading stores information.</h4>');

            table = $('#dtable').DataTable({
                processing: true,
                serverSide: false,
                data: orderItems,
                dom: '<"row"<"col"bl><"col"><"col">><t><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        text: 'Reload',
                        action: function (e, dt, node, config) {
                            window.location.reload();
                        }
                    }
                ],
                language: {
                    emptyTable: "Data loading or not available in table"
                },
                initComplete: function () {
                    $('#dtable_filter input').focus();
                },
                order: [[1, "desc"]],
                bLengthChange: false,

                columns: [
                    {
                        data: null, render:
                            function (data, type, row, meta) {
                                return data.order_id + '<input type="hidden" value="' + data.cart_id.store_key + ':' + data.order_id + '" class="' + data.cart_id.store_key + ':' + data.order_id + '">';
                            }, orderable: false
                    },
                    {
                        data: null, render:
                            function (data, type, row, meta) {
                                return type === 'sort' ? data.create_at.value : moment(data.create_at.value).format('lll');
                            }, orderable: true
                    },
                    {
                        data: null, render:
                            function (data, type, row, meta) {
                                let imgName = data.cart_id.cart_info.cart_name.toLowerCase().replace(/ /g, "_");
                                return '<div style="float: left"><span class="cartImage circle-int ' + imgName + '"></span></div>' +
                                    '<div class="cartInfo">' +
                                    '<a href="' + data.cart_id.url + '">' + data.cart_id.url + '</a><br>' +
                                    '<small>' + data.cart_id.stores_info.store_owner_info.owner + '<br>' +
                                    data.cart_id.stores_info.store_owner_info.email + '</small>' +
                                    '</div>';
                            }, orderable: false
                    },
                    {
                        data: null, render:
                            function (data, type, row, meta) {
                                return data.customer.email + '<br><small class="text-muted">' + data.customer.first_name + ' ' + data.customer.last_name + '</small>';
                            }, orderable: false
                    },
                    {
                        data: null, 'width': '300px', render:
                            function (data, type, row, meta) {

                                let state = (data.shipping_address.state) ? data.shipping_address.state.code : '';
                                let country = (data.shipping_address.country) ? data.shipping_address.country.name : '';

                                return data.shipping_address.first_name + ' ' + data.shipping_address.last_name + '<br>' +
                                    data.shipping_address.address1 + '<br>' +
                                    data.shipping_address.city + ', ' + state + '<br>' +
                                    country;
                            }, orderable: false
                    },
                    {data: null, render: 'status.name'},
                    {
                        data: null, render: function (data, type) {
                            if (type === 'display') {
                                return mappedShipmentStatuses[data.shipment_status];
                            }

                            return data.shipment_status;
                        },
                    },
                    {
                        data: null, render: function (data, type, row, meta) {
                            let total = (data.totals) ? data.totals.total : '';
                            let currency = (data.currency) ? data.currency['iso3'] : '';
                            return total + ' ' + currency;
                        }, orderable: false
                    },
                    {
                        data: null, render: function (data, type, row, meta) {
                            return '<a href="#" class="text-primary infoItem" title="Shipment Information" data-id="' + data.order_id + '" data-name="Shipments for order #' + data.order_id + '" data-action="/orders/' + data.cart_id.store_key + '/' + data.order_id + '/shipments"><i class="fas fa-info-circle"></i></a> ' +
                                '<a href="javascript:void(0);" class="text-primary addShipment"' +
                                    'title="Add new Shipment"' +
                                    'onclick="addOrderShipment(\'' + data.cart_id.store_key + '\',\'' + data.order_id +'\',' + meta.row + ')">' +
                                    '<i class="fas fa-shipping-fast"></i>' +
                                '</a> ';
                        }, orderable: false
                    }
                ],
                drawCallback: function (settings) {
                    $('[data-toggle="popover"]').popover('hide');
                    $('[data-toggle="popover"]').popover({
                        html: true
                    });

                    reinitActions();
                }
            });

            $('#_btnCheckNewOrder').click(function () {
                $(this).prop('disabled', true);
                checkNewOrders();
                return false;
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">

                <div class="card">
                    <div class="card-header">Automatic creation of shipments
                        <span class="ajax_status"></span>
                        <span class="ajax_info"></span>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h1>Automatic creation of shipments workflow</h1>
                                <p>Example: add shipments to orders from various eCommerce stores and update their statuses automatically</p>
                                <p>Adding shipments automatically to orders and updating order statuses are probably the main challenge of shipping management,
                                    order and inventory management, ERP, warehouse management software providers. With API2Cart
                                    <a target="_blank" href="/docs/#/order/OrderAdd" rel="noopener noreferrer">order.add</a> webhook notification,
                                    <a target="_blank" href="/docs/#/order/OrderShipmentAdd" rel="noopener noreferrer">order.shipment.add</a> and
                                    <a target="_blank" href="/docs/#/order/OrderUpdate" rel="noopener noreferrer">order.update</a>
                                    methods you can do it easily!</p>
                                <p class="text-center"><img class="img-fluid" src="{{ asset('images/shipment-creation.jpg') }}" style="max-height: 300px;"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button class="btn btn-primary" id="_btnCheckNewOrder">Check for new orders</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col text-right api_log">
                                <a href="#" id="showApiLog">Performed <span>0</span> requests with API2Cart. Click to
                                    see details...</a><br>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="dtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Date</th>
                                    <th>Store</th>
                                    <th>Customer</th>
                                    <th>Shipping Address</th>
                                    <th>Status</th>
                                    <th>Shipment Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
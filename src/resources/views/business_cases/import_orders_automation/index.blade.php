@extends('layouts.app')

@section('script')
    <script type="text/javascript">


        function loadData(){

            items = [];

            return axios({
                method: 'post',
                url: '{{ route('stores.list') }}',
                data: {
                    length: 10,
                    start: 0
                }
            }).then(function (response) {

                stores = response.data.data;

                if ( response.data.log ){
                    for (let k=0; k<response.data.log.length; k++){
                        logItems.push( response.data.log[k] );
                    }
                    calculateLog();
                }

                if ( stores.length == 0 ){
                    Swal.fire(
                        'Error!',
                        'Do not have store info, please check API log.',
                        'error'
                    );
                    $.unblockUI();
                    return;
                }


                for (let i=0; i<stores.length; i++){

                    blockUiStyled('<h3>Loading '+ stores[i].url +' information.</h3>');

                    axios({
                        method: 'post',
                        url: '{{ route('orders.list') }}/'+stores[i].store_key,
                        data: {
                            length: 10,
                            start: 0
                        }
                    }).then(function (rep) {

                        //console.log( stores[i] );

                        let orders = rep.data.data;
                        let logs = rep.data.log;

                        blockUiStyled('<h3>Adding '+ stores[i].url +' orders.</h3>');

                        $.each( orders , function( index, value ) {
                            value.cart_id = stores[i];
                            items.push( value );
                        });

                        //update log count
                        if ( rep.data.log ){
                            for (let k=0; k<rep.data.log.length; k++){
                                logItems.push( rep.data.log[k] );
                            }
                            calculateLog();
                        }



                        var datatable = $( '#dtable' ).dataTable().api();

                        datatable.clear();
                        datatable.rows.add( items );
                        datatable.order([ 1, "asc" ]).draw();


                        $.unblockUI();

                        $.growlUI('Notification', stores[i].url + ' data loaded successfull!', 500);


                    });


                }




            }).catch(function (error) {
                // handle error
                // console.log(error.response);

                if ( error.response.data.log ){
                    for (let k=0; k<error.response.data.log.length; k++){
                        logItems.push( error.response.data.log[k] );
                    }
                    calculateLog();
                }

                $.unblockUI();

                Swal.fire(
                    'Error!',
                    'Do not have store info, please check API log.',
                    'error'
                )

            });
        }

        function loadCustomers(store_key)
        {
            return axios.post( '/customers/list/' + store_key );
        }

        function loadProducts(store_key)
        {
            return axios.post( '/products/list/' + store_key );
        }

        function addOrder()
        {
            let action = "{{ route('orders.create') }}";

            axios.get( action )
                .then(function (response) {
                    // handle success
                    // console.log(response);

                    selectItems = response.data.item;

                    Swal.fire({
                        title: 'Add new Order',
                        html: response.data.data,
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger'
                        },
                        showCancelButton: true,
                        showCloseButton: true,
                        confirmButtonText: 'Create',
                        width: '70%',
                        allowOutsideClick: false,
                        preConfirm: ( pconfirm ) => {

                            $('.swal2-content').find('.is-invalid').removeClass('is-invalid');

                            let fact = $('.swal2-content form')[0].action;
                            var formData = getFormData( $('.swal2-content form') );


                            return axios.post( fact , formData , {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            })
                                .then(function (presponse) {

                                    //TODO: store added
                                    console.log( presponse );

                                    return true;
                                })
                                .catch(function (error) {
                                    // console.log( error );

                                    if ( typeof error.response.data.errors != 'undefined'){

                                        $.each(error.response.data.errors, function(index, value) {
                                            if (typeof index !== 'undefined' || typeof value !== 'undefined'){

                                                let obj = $( document.getElementById(index) );
                                                let err = $(obj).parent().parent().find('.invalid-feedback');
                                                $(err).empty().append( value.shift() );
                                                $(obj).addClass('is-invalid')
                                            }

                                        });

                                    }


                                    return false;
                                });


                        },
                    });

                    $('#cart_id').change(function(e){

                        let selected = this.value;
                        let item = Object.values( selectItems ).find( obj =>{ return obj.store_key === selected } );

                        $('#addItemFields').empty();
                        $('#customer_id').empty();
                        $('#productsList').empty();
                        $( "#customer_id" ).prop( "disabled", true );

                        blockUiStyled('<h3>Loading store customers and products.</h3>');

                        axios.all([loadCustomers( item.store_key ), loadProducts( item.store_key )])
                            .then(axios.spread(function ( users, products) {
                                // Both requests are now complete

                                if (users.data.data.length){
                                    $.each(users.data.data, function(key, value) {
                                        $('#customer_id')
                                            .append($("<option></option>")
                                                .attr("value", value.id )
                                                .text( value.first_name +' '+ value.last_name +'[ ' + value.email +' ]' ));
                                    });
                                    $( "#customer_id" ).prop( "disabled", false );
                                }

                                if ( products.data.data.length ){
                                    $.each(products.data.data, function(key, value) {

                                        let html = '<label class="col-lg-6">\n' +
                                            '                        <input type="checkbox" name="checked_id[]" class="card-input-element d-none" value="'+value.id+'">\n' +
                                            '                        <div class="card card-body bg-light d-flex ">\n' +
                                            '                            <h5>'+value.name+'</h5>\n' +
                                            '                            <small>\n' +
                                            '                                Price: ' +value.price +' '+ value.currency +
                                            '                            </small>\n' +
                                            '                            <small>\n' +
                                            '                                Quantity: <input type="number" name="product_quantity[]"  min="0" max="'+ value.quantity +'" step="1" value="0">\n' +
                                            '                            </small>\n' +
                                            '                        <input type="hidden" name="product_id[]" value="'+value.id+'">\n' +
                                            '                        </div>\n' +
                                            '                    </label>';

                                        $('#productsList').append( html );

                                    });
                                }

                                // console.log( products );

                                $.unblockUI();
                            }));


                    });



                    //update log count
                    if ( response.data.log ){
                        for (let k=0; k<response.data.log.length; k++){
                            logItems.push( response.data.log[k] );
                        }
                        calculateLog();

                    }
                    $.unblockUI();

                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                    $.unblockUI();

                    Swal.fire(
                        'Error!',
                        'Failed info ' + id,
                        'error'
                    )

                });
        }



        var table;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            blockUiStyled('<h3>Loading stores information.</h3>');

            loadData();

            table = $('#dtable').DataTable( {
                processing: true,
                serverSide: false,
                // ordering: false,
                data: items,
                dom: '<"row"<"col"><"col"><"col">><t><"row"<"col"i><"col">>',
                buttons: [
                    {
                        text: 'Reload',
                        action: function ( e, dt, node, config ) {

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
                order: [[ 1, "desc" ]],

                iDisplayLength: 10,
                bLengthChange: false,

                columns: [
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.order_id + '<input type="hidden" value="'+ data.cart_id.store_key +':'+ data.order_id +'" class="'+ data.cart_id.store_key +':'+ data.order_id +'">';
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return moment(data.create_at.value).format('MMMM Do YYYY HH:mm');
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                let imgName = data.cart_id.cart_info.cart_name.toLowerCase().replace(/ /g,"_");
                                return '<img class="cartImage" src="https://api2cart.com/wp-content/themes/api2cart/images/logos/'+imgName+'.png"><br>' +
                                    '<a href="'+data.cart_id.url+'">'+data.cart_id.url+'</a><br>'+
                                    '<small>'+data.cart_id.stores_info.store_owner_info.owner+'</small><br>'+
                                    '<small>'+data.cart_id.stores_info.store_owner_info.email+'</small>';
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.customer.email + '<br><small class="text-muted">'+data.customer.first_name +' '+data.customer.last_name+'</small>';
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){

                                let state = (data.shipping_address.state) ? data.shipping_address.state.code : '';
                                let country = (data.shipping_address.country) ? data.shipping_address.country.name : '';

                                return data.shipping_address.first_name +' '+data.shipping_address.last_name +'<br>'+
                                    data.shipping_address.address1+'<br>'+
                                    data.shipping_address.city+', '+state+'<br>'+
                                    country;
                            }, orderable : false
                    },
                    { data: null, render: 'status.name' },
                    { data: null, render: function ( data, type, row, meta ){
                            let total = (data.totals) ? data.totals.total : '';
                            let currency = (data.currency) ? data.currency['iso3'] : '';
                            return total + ' ' + currency;
                        }, orderable : false
                    },
                    {
                        data: null, render: function ( data, type, row, meta ){
                            return '<i class="far fa-file-alt"></i> ' +
                                '<i class="fas fa-edit"></i> '+
                                '<a href="#" class="text-primary infoItem" title="Shipment Information" data-id="'+data.order_id+'" data-name="Order #'+data.order_id+'" data-action="/orders/'+data.cart_id.store_key+'/'+data.order_id+'"><i class="fas fa-shipping-fast"></i></a> '+
                                '<a href="#" class="text-primary productsItem" title="Products" data-id="'+data.order_id+'" data-name="Order #'+data.order_id+'" data-action="/orders/'+data.cart_id.store_key+'/'+data.order_id+'/products"><i class="fas fa-shopping-cart"></i></a> ';
                        }, orderable : false
                    }
                ],
                drawCallback: function( settings ) {
                    $('[data-toggle="popover"]').popover('hide');
                    $('[data-toggle="popover"]').popover({
                        html: true
                    });
                    reinitActions();
                }
            } );


            $('#_btnCreateOrder').click(function(){

                addOrder();

                return false;
            });


        } );
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">

                <div class="card">
                    <div class="card-header">Orders <span class="ajax_status"></span></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h1>Import order automation workflow</h1>
                                <p>Example: import customer orders from multiple stores and marketplaces into your platform</p>
                                <p>Setting automated order import from e-stores is probably the main challenge of ERP, shipping, warehouse, order and inventory software owners. With API2Cart <a target="_blank" href="https://docs.api2cart.com/order-list">order.list</a> method and webhook for <a target="_blank" href="https://docs.api2cart.com/order-add">order.add</a> event you can do it easily!</p>
                                <p class="text-center"><img class="img-fluid" src="{{ asset('images/import-orders-1.jpg') }}" style="max-height: 300px;"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button class="btn btn-outline-secondary" id="_btnCreateOrder">Create test order</button>
                                <button class="btn btn-outline-secondary ">Check for newt orders</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col text-right api_log">
                                <a href="#" id="showApiLog" >Performed <span>0</span> requests with API2Cart. Click to see details...</a><br>
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

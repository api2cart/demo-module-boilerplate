@extends('layouts.app')

@section('script')
    <script type="text/javascript">

        var curentProducts = [];

        function loadData(created_from=null){

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
                            start: 0,
                            limit: 3,
                            sort_by: 'create_at',
                            sort_direct: 'desc',
                            created_from: created_from
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
                        datatable.order([ 1, "desc" ]).draw();


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

        function loadStatuses(store_key)
        {
            return axios.post( '/orders/statuses/' + store_key );
        }

        function productQuantityProcess()
        {
            $('.product_quantity').unbind();
            $('.product_quantity').change(function(){
                let quantity = $(this).val();
                let check = $(this).parent().parent().parent().find('.d-none');

                if ( quantity == 0 ){
                    $(check).prop('checked', false);
                } else {
                    $(check).prop('checked', true);
                }

                calculateTotalPrice();

            });

        }

        function calculateTotalPrice()
        {
            let store_id = $('#cart_id').val();
            let total = 0;
            $.each( $('.product_quantity'), function(key, value) {
                let check = $(this).parent().parent().parent().find('.d-none');
                let quantity = $(this).val();
                if ( quantity > 0 ){
                    let price = curentProducts.find(el => el.id === $(check).val() )['price'];
                    total += price;
                }
            });
            $('#product_total').val( total );
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
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-danger'
                        },
                        showCancelButton: true,
                        showCloseButton: true,
                        buttonsStyling: false,
                        confirmButtonText: 'Create',
                        width: '70%',
                        allowOutsideClick: false,
                        preConfirm: ( pconfirm ) => {

                            $('.swal2-content').find('.is-invalid').removeClass('is-invalid');
                            $( $(document.getElementById('_form_errors')).parent() ).hide();

                            let fact = $('.swal2-content form')[0].action;
                            let store_key = $('#cart_id').val();
                            var formData = getFormData( $('.swal2-content form') );

                            $.each( $('.product_quantity'), function(key, value) {

                                let check = $(this).parent().parent().parent().find('.d-none');
                                let quantity = $(this).val();

                                if ( quantity == 0 ){
                                    $(check).prop('checked', false);
                                } else {
                                    $(check).prop('checked', true);
                                }
                            });



                            return axios.post( fact , formData , {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            })
                                .then(function (presponse) {

                                    // console.log( presponse );

                                    let st = stores.find(el => el.store_key === store_key);
                                    let newItem = presponse.data.item;

                                    newItem.cart_id = st;
                                    items.push( newItem );

                                    var datatable = $( '#dtable' ).dataTable().api();
                                    datatable.clear();
                                    datatable.rows.add( items );
                                    datatable.order([ 1, "desc" ]).draw();

                                    datatable.rows().every(function(){
                                        var tobj  = this;
                                        var tnode = tobj.node();
                                        var tdata = tobj.data();

                                        if ( tdata.cart_id.store_key == st.store_key && tdata.id == newItem.id ){
                                            $(tnode).addClass('table-info');
                                        }
                                    });


                                    // let lobj = $( '#dtable' ).find('.'+store_key+':'+newItem.order_id);
                                    //
                                    // console.log( lobj );
                                    //
                                    // if ( typeof lobj != 'undefined'){
                                    //     $(lobj).parent().parent().addClass('table-info');
                                    // }


                                    return true;
                                })
                                .catch(function (error) {

                                    // console.log( error.response.data.errors.checked_id );
                                    if( typeof error.response.data.errors.checked_id != 'undefined' ){
                                        $('#_form_errors').empty().append( error.response.data.errors.checked_id[0] )
                                        $( $(document.getElementById('_form_errors')).parent() ).show();
                                        $( $(document.getElementById('_form_errors')).parent() ).fadeOut(9000);
                                    }


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
                        $('#status_id').empty();
                        $('#customer_id').prop( "disabled", true );

                        blockUiStyled('<h3>Loading store customers and products.</h3>');

                        axios.all([ loadCustomers( item.store_key ), loadProducts( item.store_key ), loadStatuses( item.store_key ) ])
                            .then(axios.spread(function ( users, products, statuses) {
                                // Both requests are now complete

                                if (users.data.data.length){
                                    $.each(users.data.data, function(key, value) {
                                        $('#customer_id')
                                            .append($("<option></option>")
                                                .attr("value", value.id )
                                                .text( value.first_name +' '+ value.last_name +'[ ' + value.email +' ]' ));
                                    });
                                    $('#customer_id').prop( "disabled", false );
                                }

                                // console.log( statuses );
                                if (statuses.data.data.length){
                                    $.each(statuses.data.data, function(key, value) {
                                        $('#status_id')
                                            .append($("<option></option>")
                                                .attr("value", value.id )
                                                .text( value.name ));
                                    });
                                    $('#status_id').prop( "disabled", false );
                                }

                                if ( products.data.data.length ){

                                    curentProducts = products.data.data;

                                    $.each(curentProducts, function(key, value) {

                                        let html = '<label class="col-lg-6">\n' +
                                            '                        <input type="checkbox" name="checked_id[]" class="card-input-element d-none" value="'+value.id+'">\n' +
                                            '                        <div class="card card-body bg-light d-flex ">\n' +
                                            '                            <h5>'+value.name+'</h5>\n' +
                                            '                            <small>\n' +
                                            '                                Price: ' +value.price +' '+ value.currency +
                                            '                            </small>\n' +
                                            '                            <small>\n' +
                                            '                                Quantity: <input type="number" class="product_quantity" name="product_quantity[]"  min="0" max="'+ value.quantity +'" step="1" value="0">\n' +
                                            '                            </small>\n' +
                                            '                        <input type="hidden" name="product_id[]" value="'+value.id+'">\n' +
                                            '                        </div>\n' +
                                            '                    </label>';

                                        $('#productsList').append( html );

                                    });

                                    productQuantityProcess();
                                }

                                // console.log( products );

                                $.unblockUI();
                            }));


                    });

                    $('#cart_id').change(function(){
                        $(this).removeClass('is-invalid');
                        $('#customer_id,#status_id').removeClass('is-invalid');
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

        function checkNewOrders()
        {
            // console.log('check for new orders');
            blockUiStyled('<h3>Loading new orders.</h3>');

            let datatable = $( '#dtable' ).dataTable().api();
            let last_order = datatable.column( 1,{order:'applied'} ).data()[0].create_at.value;

            $.each( stores , function( i, stor ) {
                blockUiStyled('<h3>Loading '+ stor.url +' information.</h3>');

                axios({
                    method: 'post',
                    url: '{{ route('orders.list') }}/'+stor.store_key,
                    data: {
                        length: 10,
                        start: 0,
                        limit: 3,
                        sort_by: 'create_at',
                        sort_direct: 'desc',
                        created_from: last_order
                    }
                }).then(function (rep) {

                    //console.log( stores[i] );

                    let orders = rep.data.data;
                    let logs = rep.data.log;

                    if ( rep.data.log ){
                        for (let k=0; k<rep.data.log.length; k++){
                            logItems.push( rep.data.log[k] );
                        }
                        calculateLog();

                    }

                    $.each( orders , function( oi, order ) {

                        order.cart_id = stor;

                        let elexist =  items.find(el => el.id == order.id && el.cart_id.store_key == stor.store_key );

                        if ( typeof elexist == 'undefined' ){

                            // add new order to table and highlight it
                            items.push( order );

                            datatable.clear();
                            datatable.rows.add( items );
                            datatable.order([ 1, "desc" ]).draw();

                            datatable.rows().every(function(){
                                var tobj  = this;
                                var tnode = tobj.node();
                                var tdata = tobj.data();
                                if ( tdata.cart_id.store_key == stor.store_key && tdata.id == order.id ){
                                    $(tnode).addClass('table-info');
                                }
                            });


                        }


                    });



                    $.unblockUI();

                    $.growlUI('Notification', stores[i].url + ' data loaded successfull!', 500);


                });


            });




            // $.unblockUI();
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
                dom: '<"row"<"col"bl><"col"><"col">><t><"row"<"col"i><"col">>',
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

                // iDisplayLength: 10,
                // bLengthChange: false,

                columns: [
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.order_id + '<input type="hidden" value="'+ data.cart_id.store_key +':'+ data.order_id +'" class="'+ data.cart_id.store_key +':'+ data.order_id +'">';
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return type === 'sort' ? data.create_at.value : moment(data.create_at.value).format('lll');
                                // return moment(data.create_at.value).format('D/MM/YYYY HH:mm');
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
                            return '<a href="#" class="text-primary infoItem" title="Shipment Information" data-id="'+data.order_id+'" data-name="Order #'+data.order_id+'" data-action="/orders/'+data.cart_id.store_key+'/'+data.order_id+'"><i class="fas fa-shipping-fast"></i></a> '+
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
            $('#_btnCheckNewOrder').click(function(){
                checkNewOrders();
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
                    <div class="card-header">Import order automation <span class="ajax_status"></span></div>

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
                                <button class="btn btn-primary" id="_btnCreateOrder">Create test order</button>
                                <button class="btn btn-primary" id="_btnCheckNewOrder">Check for newt orders</button>
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

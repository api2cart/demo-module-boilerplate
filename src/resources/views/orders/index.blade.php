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

                    Swal.fire({
                        title: 'Error!',
                        text: 'Do not have store info, please check API log.',
                        icon: 'error',

                        buttonsStyling: false,
                        confirmButtonClass: 'btn btn-primary btn-lg',
                        cancelButtonClass: 'btn btn-lg',
                    })

                    $.unblockUI();
                    return;
                }


                for (let i=0; i<stores.length; i++){

                    blockUiStyled('<h4>Loading '+ stores[i].url +' information.</h4>');

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

                        blockUiStyled('<h4>Adding '+ stores[i].url +' orders.</h4>');

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

                        initFilters();

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

                Swal.fire({
                    title: 'Error!',
                    text: 'Do not have store info, please check API log.',
                    icon: 'error',

                    buttonsStyling: false,
                    confirmButtonClass: 'btn btn-primary btn-lg',
                    cancelButtonClass: 'btn btn-lg',
                })

            });
        }


        function initFilters()
        {
            var names = getUniqueName();
            var status= getUniqueStatus();
            var store = getUniqueStore();

            yadcf.init( table , [
                {
                    column_number: 3,
                    select_type: 'select2',
                    data: names,
                    select_type_options: { width: '200px' }
                },
                {
                    column_number: 5,
                    select_type: 'select2',
                    data: status,
                },
                {
                    column_number: 2,
                    select_type: 'select2',
                    data: store,
                    select_type_options: { width: '200px' }
                },

            ]);

        }


        function getUniqueName()
        {
            var uniqueItem = [];
            items.filter(function(item){
                let name = item.customer.first_name +' '+item.customer.last_name;
                if (!~uniqueItem.indexOf(name)) {
                    uniqueItem.push(name);
                    return item;
                }
            });
            return uniqueItem;
        }


        function getUniqueStatus()
        {
            var uniqueItem = [];
            items.filter(function(item){
                if (!~uniqueItem.indexOf(item.status.name)) {
                    uniqueItem.push(item.status.name);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniqueOwner()
        {
            var uniqueItem = [];
            items.filter(function(item){
                let email = (item.cart_id.stores_info.store_owner_info.email) ? item.cart_id.stores_info.store_owner_info.email : '';
                if (!~uniqueItem.indexOf(email)) {
                    uniqueItem.push(email);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniqueStore()
        {
            var uniqueItem = [];
            items.filter(function(item){
                let url = (item.cart_id.url) ? item.cart_id.url : '';
                if (!~uniqueItem.indexOf(url)) {
                    uniqueItem.push(url);
                    return item;
                }
            });
            return uniqueItem;
        }




        var table;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            blockUiStyled('<h4>Loading stores information.</h4>');

            loadData();

            // $.unblockUI();

            // console.log( items );


            table = $('#dtable').DataTable( {
                processing: true,
                serverSide: false,
                // ordering: false,
                data: items,
                dom: '<"row"<"col"B><"col"l><"col"f>><t><"row"<"col"i><"col">p>',
                buttons: [
                    {
                        text: 'Reload',
                        action: function ( e, dt, node, config ) {

                            window.location.reload();

                        },
                        className: 'btn btn-primary'
                    }
                ],
                language: {
                    emptyTable: "Data loading or not available in table"
                },
                initComplete: function () {
                    $('#dtable_filter input').focus();
                },
                "order": [[ 1, "desc" ]],
                columns: [
                    { data: null, render: 'order_id' },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return type === 'sort' ? data.create_at.value : moment(data.create_at.value).format('L');
                            }
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                let imgName = data.cart_id.cart_info.cart_name.toLowerCase().replace(/ /g,"_");
                                return '<div style="float: left"><span class="cartImage circle-int ' + imgName + '"></span></div>' +
                                       '<div class="cartInfo">' +
                                           '<a href="'+data.cart_id.url+'">'+data.cart_id.url+'</a><br>'+
                                           '<small>'+data.cart_id.stores_info.store_owner_info.owner+'<br>'+
                                           data.cart_id.stores_info.store_owner_info.email+'</small>' +
                                       '</div>';
                            }
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.customer.email + '<br><small class="text-muted">'+data.customer.first_name +' '+data.customer.last_name+'</small>';
                            }
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){

                                let state = (data.shipping_address.state) ? data.shipping_address.state.code : '';
                                let country = (data.shipping_address.country) ? data.shipping_address.country.name : '';

                                return data.shipping_address.first_name +' '+data.shipping_address.last_name +'<br>'+
                                    data.shipping_address.address1+'<br>'+
                                    data.shipping_address.city+', '+state+'<br>'+
                                    country;
                            }
                    },
                    { data: null, render: 'status.name' },
                    { data: null, render: function ( data, type, row, meta ){
                            let total = (data.totals) ? data.totals.total : '';
                            let currency = (data.currency) ? data.currency['iso3'] : '';
                            return total + ' ' + currency;
                        }
                    },
                    {
                        data: null, render: function ( data, type, row, meta ){
                            // return '<i class="far fa-file-alt"></i> ' +
                            //     '<i class="fas fa-edit"></i> '+
                                return '<a href="#" class="text-primary infoItem" title="Shipment Information" data-id="'+data.order_id+'" data-name="Order #'+data.order_id+'" data-action="/orders/'+data.cart_id.store_key+'/'+data.order_id+'"><i class="fas fa-shipping-fast"></i></a> '+
                                '<a href="#" class="text-primary productsItem" title="Products" data-id="'+data.order_id+'" data-name="Order #'+data.order_id+'" data-action="/orders/'+data.cart_id.store_key+'/'+data.order_id+'/products"><i class="fas fa-shopping-cart"></i></a> ';
                        }, orderable : false
                    }
                ],
                "drawCallback": function( settings ) {
                    $('[data-toggle="popover"]').popover('hide');
                    $('[data-toggle="popover"]').popover({
                        html: true
                    });
                    reinitActions();
                }
            } );



        } );
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">Orders <span class="ajax_status"></span>
                        <span class="float-right"><a target="_blank" href="https://docs.api2cart.com/post/interactive-docs?version=v1.1#operations-tag-order">Read Orders API methods</a></span>
                    </div>

                    <div class="card-body">
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

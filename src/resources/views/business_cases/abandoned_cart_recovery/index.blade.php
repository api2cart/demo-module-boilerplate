@extends('layouts.app')

@section('script')
    <script type="text/javascript">

        var rows_selected = [];

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

                    blockUiStyled('<h4>Loading '+ stores[i].url +' information.</h4>');

                    axios({
                        method: 'post',
                        url: '{{ route('orders.abandoned') }}/'+stores[i].store_key,
                        data: {
                            length: 10,
                            start: 0,
                            limit: 3,
                            sort_by: 'create_at',
                            sort_direct: 'desc',
                            created_from: created_from
                        }
                    }).then(function (rep) {


                        let orders = rep.data.data;
                        let logs = rep.data.log;

                        blockUiStyled('<h4>Adding '+ stores[i].url +' abandoned orders.</h4>');

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

        var table;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            blockUiStyled('<h4>Loading stores information.</h4>');

            loadData();

            table = $('#dtable').DataTable( {
                processing: true,
                serverSide: false,
                // ordering: false,
                data: items,
                dom: '<"row"<"col"><"col"l><"col">><t><"row"<"col"i><"col num_selected"><"col">>',
                buttons: [
                    {
                        text: 'Reload',
                        action: function ( e, dt, node, config ) {

                            window.location.reload();

                        }
                    }
                ],
                language: {
                    emptyTable: "<h5>Looks you do not have any abandoned carts.</h5><br><h5>Please register in any store and add et least one product to cart, but do not byu it. And try again.</h5>"
                },
                initComplete: function () {
                    $('#dtable_filter input').focus();
                },
                order: [[ 1, "desc" ]],
                columnDefs: [
                    {
                        'targets': 0,
                        'checkboxes': {
                            'selectAll': true,
                            'selectRow': true,
                            'selectAllPages': false,
                            'selectCallback': function(nodes,selected){
                                // console.log( nodes, selected  );
                                rows_selected = [];
                                table.rows().every(function(index, element ){
                                    var tnode = this.node();

                                    if ( $(tnode).find('input.dt-checkboxes').is(':checked') ){
                                        rows_selected.push( $(tnode).find('input.dt-checkboxes').val() );
                                    }

                                });

                                $(".num_selected").empty();

                                if (rows_selected.length){
                                    $(".num_selected").append( "Selected: " + rows_selected.length );
                                }
                                // console.log( rows_selected );

                            },
                            // 'selectAllCallback': function(nodes,selected){
                            //     // console.log( nodes);
                            //
                            // },
                        }
                    }
                ],
                select: {
                    'style': 'multi',
                },
                columns: [
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return  '<input type="checkbox" class="dt-checkboxes" value="'+ data.cart_id.store_key +':'+ data.customer.id +'" class="'+ data.cart_id.store_key +':'+ data.customer.id +'">';
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
                    { data: null, render: function ( data, type, row, meta ){
                            let owner = (data.cart_id.stores_info.store_owner_info.owner) ? data.cart_id.stores_info.store_owner_info.owner : '';
                            let email = (data.cart_id.stores_info.store_owner_info.email) ? data.cart_id.stores_info.store_owner_info.email : '';
                            return owner+'<br><small>'+email+'</small><br><small>Store Key: '+data.cart_id.store_key+'</small>';
                    }},
                    { data: null, render:
                            function ( data, type, row, meta ){
                                var prod = '';
                                if ( typeof data.order_products != 'undefined' ){
                                    prod = '<ul>';
                                    $.each( data.order_products , function( index, value ) {
                                        prod += '<li>'+ value.name +'</li>';
                                    });
                                    prod += '</ul>';
                                }
                                return prod;
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.customer.email + '<br><small class="text-muted">'+data.customer.first_name +' '+data.customer.last_name+'</small>';
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                var sumbol = ( typeof data.currency.symbol_left != 'undefined' && data.currency.symbol_left != '') ? data.currency.symbol_left : '';
                                var curren = ( typeof data.currency.symbol_left != 'undefined' && data.currency.symbol_left == '') ? data.currency.iso3 : '';
                                return  sumbol +' '+ data.totals.total +' '+ curren;
                            }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return type === 'sort' ? data.created_at.value : moment(data.created_at.value).format('lll');
                                // return moment(data.create_at.value).format('D/MM/YYYY HH:mm');
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


            $('#_btnCheckAbandoned').click(function(){
                blockUiStyled('<h4>Check for abandoned</h4>');
                $('#products-tab').click();
                $('input:checkbox').prop('checked', false);
                rows_selected = [];
                loadData();
                return false;
            });

            $('#_btnCrateAbandonedMail').click(function(){
                if ( rows_selected.length < 1 ){
                    Swal.fire(
                        'Error!',
                        'Please be sure you selected one or more row.',
                        'error'
                    )
                    return false;
                }

                Swal.fire({
                    title: 'Submit your email address',
                    input: 'email',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Send',
                    showLoaderOnConfirm: true,
                    preConfirm: (email) => {


                        blockUiStyled('<h4>Sending email....</h4>');

                        axios({
                            method: 'post',
                            url: '{{ route('businessCases.abandoned_cart_recovery.send') }}',
                            data: {
                                items: rows_selected,
                                email: email
                            }
                        }).then(function (rep) {

                            $.unblockUI();

                            Swal.fire(
                                'OK!',
                                'Email was send.',
                                'success'
                            );

                            $('input:checkbox').prop('checked', false);
                            rows_selected = [];

                        });


                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });


                return false;
            });

            $('#_btnGenerateEmail').click(function(){

                if ( rows_selected.length < 1 ){
                    $('#template-tab').prop( "disabled", true );
                    $('#template-tab').addClass('disabled');

                    Swal.fire(
                        'Error!',
                        'Please be sure you selected one or more abandoned.',
                        'error'
                    )
                    return false;
                }

                $('#template-tab').prop( "disabled", false );
                $('#template-tab').removeClass('disabled');
                $('#template-tab').click();



                $('#_mailTemplate').contents().find("body").empty();

                axios({
                    method: 'post',
                    url: '{{ route('businessCases.abandoned_cart_recovery.compose') }}',
                    data: {
                        items: rows_selected,
                    }
                }).then(function (rep) {

                    // console.log( rep );
                    $('#_mailTemplate').contents().find("body").append( rep.data );

                });

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
                    <div class="card-header">Abandoned cart recovery <span class="ajax_status"></span></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h1>Abandoned cart recovery workflow</h1>
                                <p>Example: send automatic notifications to recover lost eCommerce sales</p>
                                <p>For chatbots, email marketing and cart abandonment software, it is essential to be able to retrieve information on abandoned orders from the shopping carts and send abandoned cart emails. With API2Cart <a href="https://docs.api2cart.com/order-abandoned-list" target="_blank">order.abandoned.list</a> method you can do it easily!</p>
                                <p class="text-center"><img class="img-fluid" src="{{ asset('images/abandon-cart1.png') }}" style="max-height: 300px;"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button class="btn btn-primary" id="_btnCheckAbandoned">Get Abandoned carts</button>
                                <button class="btn btn-primary" id="_btnGenerateEmail">Generate Email</button>
                                <button class="btn btn-primary" id="_btnCrateAbandonedMail">Create Abandoned cart followups</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col text-right api_log">
                                <a href="#" id="showApiLog" >Performed <span>0</span> requests with API2Cart. Click to see details...</a><br>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="products-tab" data-toggle="tab" href="#products" role="tab" aria-controls="home" aria-selected="true">Abandoned</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link disabled" disabled id="template-tab" data-toggle="tab" href="#template" role="tab" aria-controls="contact" aria-selected="false">Mail Template</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <br>
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade show active" id="products" role="tabpanel" aria-labelledby="products-tab">
                                <div class="row">
                                    <div class="col">
                                        <div class="table-responsive">
                                            <table id="dtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Store</th>
                                                    <th>Store Owner</th>
                                                    <th>Products</th>
                                                    <th>Customer</th>
                                                    <th>Total</th>
                                                    <th>Created</th>
                                                </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="template" role="tabpanel" aria-labelledby="template-tab">
                                <iframe id="_mailTemplate" src="" width="100%" height="800" frameborder="0" ></iframe>
                            </div>
                        </div>





                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

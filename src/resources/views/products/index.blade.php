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

                    blockUiStyled('<h4>Loading '+ stores[i].url +' information.</h4>');

                    axios({
                        method: 'post',
                        url: '{{ route('products.list') }}/'+stores[i].store_key,
                        data: {
                            length: 10,
                            start: 0
                        }
                    }).then(function (rep) {

                        let orders = rep.data.data;

                        blockUiStyled('<h4>Adding '+ stores[i].url +' product.</h4>');

                        for (let j=0; j<orders.length; j++){
                            orders[j].cart_id = stores[i];
                            items.push( orders[j] );
                        }


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
                        datatable.draw();

                        $.unblockUI();

                        $.growlUI('Notification', stores[i].url + ' data loaded successfull!' , 500);
                        $('[data-toggle="popover"]').popover({
                            html: true
                        });

                        reinitActions();

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

                Swal.fire(
                    'Error!',
                    'Do not have store info, please check API log.',
                    'error'
                )

            });
        }

        function initFilters()
        {
            var names = getUniqueName();
            var sku   = getUniqueSku();
            var owner = getUniqueOwner();
            var store = getUniqueStore();


            yadcf.init( table , [
                {
                    column_number: 2,
                    select_type: 'select2',
                    data: names,
                    select_type_options: { width: '200px' }
                },
                {
                    column_number: 3,
                    select_type: 'select2',
                    data: sku,
                },
                {
                    column_number: 4,
                    select_type: 'select2',
                    data: owner,
                    select_type_options: { width: '200px' }
                },
                {
                    column_number: 5,
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
                if (!~uniqueItem.indexOf(item.name)) {
                    uniqueItem.push(item.name);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniquePrice()
        {
            var uniqueItem = [];
            items.filter(function(item){
                if (!~uniqueItem.indexOf(item.price)) {
                    uniqueItem.push(item.price);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniqueSku()
        {
            var uniqueItem = [];
            items.filter(function(item){
                if (!~uniqueItem.indexOf(item.u_sku)) {
                    uniqueItem.push(item.u_sku);
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



        var rows_selected = [];
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
                dom: '<"row"<"col"B><"col"l><"col"f>><t><"row"<"col"i><"col num_selected"><"col"p>>',
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
                order: [[2, 'asc']],
                columns: [
                    { data: null, render:
                            function(data, type, row, meta){
                                return '<input type="checkbox" class="dt-checkboxes" value="'+data.cart_id.store_key+':'+data.id+'" >';
                            },orderable : false,  "searchable": false,
                    },
                    { data: null, render: function ( data, type, row, meta ){
                            let imgurl = (data.images[0])? data.images[0].http_path : '{{ asset('css/img/no_image_275x275.jpg') }}';
                            return '<img src="'+imgurl+'" style="max-width: 60px; max-height: 60px;">'
                        }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.name + '<br><small class="text-muted more" data-toggle="popover" data-trigger="hover" data-content="'+data.description.escapeHTML()+'">'+data.description.trunc(80)+'</small>';
                            }
                    },
                    { data: null, render: function ( data, type, row, meta ){
                            return (typeof data.u_sku != 'undefined') ? data.u_sku : '';
                        }},
                    { data: null, render: function ( data, type, row, meta ){
                            let owner = (data.cart_id.stores_info.store_owner_info.owner) ? data.cart_id.stores_info.store_owner_info.owner : '';
                            let email = (data.cart_id.stores_info.store_owner_info.email) ? data.cart_id.stores_info.store_owner_info.email : '';
                            return owner+'<br><small>'+email+'</small><br><small>Store Key: '+data.cart_id.store_key+'</small>';
                     }},
                    { data: null, render: function ( data, type, row, meta ){
                            let imgName = data.cart_id.cart_info.cart_name.toLowerCase().replace(/ /g,"_");
                            return '<img class="cartImage" src="https://api2cart.com/wp-content/themes/api2cart/images/logos/'+imgName+'.png"><br>' +
                                    '<a href="'+data.cart_id.url+'">'+data.cart_id.url+'</a><br>'+
                                    '<small>'+data.cart_id.cart_info.cart_name+'<small><br>'+
                                    '<small>'+data.cart_id.cart_info.cart_versions+'</small>';

                    }},
                    { data: null, render: function ( data, type, row, meta ){
                            let Pprice = '';
                            if ( typeof data.children != 'undefined' && data.children.length ){

                                $.each(data.children, function(i, v) {
                                    Pprice += v.default_price + '&nbsp;' + data.currency + '&nbsp;<i class="fas fa-tags" style="font-size: 8px;" title="This is price of product vsriant '+ v.name +'"></i><br>';
                                });

                            } else {
                                Pprice = data.price + '&nbsp;' + data.currency;
                            }
                            return Pprice;
                        }
                    },
                    {
                        data: null, render: function ( data, type, row, meta ){
                            return '<a href="#" aria-disabled="true" class="text-secondary disabled"><i class="far fa-file-alt"></i></a> ' +
                                '<a href="#"  class="text-success editItem" data-id="'+data.id+'" data-name="'+data.name+'" data-action="/products/'+data.cart_id.store_key+'/'+data.id+'/edit" data-deleteimages="/products/'+data.cart_id.store_key+'/'+data.id+'/delete_image"><i class="fas fa-edit"></i></a> ' +
                                '<a href="#"  class="text-danger deleteItem" data-id="'+data.id+'" data-name="'+data.name+'" data-action="/products/'+data.cart_id.store_key+'/'+data.id+'"><i class="fas fa-trash-alt"></i></a> ';
                        }, orderable : false
                    }
                ],
                "drawCallback": function( settings ) {
                    $('[data-toggle="popover"]').popover('hide');
                    $('[data-toggle="popover"]').popover({
                        html: true
                    });
                    reinitActions();

                    //TODO: refill filters here


                }
            } );




            // buildSelect( table );
            // table.on( 'draw', function () {
            //     buildSelect( table );
            // } );

            $('#dtable').on( 'page.dt', function () {
                $('[data-toggle="popover"]').popover('hide');
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
                    <div class="card-header">Products <span class="ajax_status"></span>
                        <span class="float-right"><a target="_blank" href="https://docs.api2cart.com/post/interactive-docs?version=v1.1#operations-tag-product">Read Products API methods</a></span>
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
                                    <th></th>
                                    <th>Image<br></th>
                                    <th>Name/Description<br></th>
                                    <th>SKU<br></th>
                                    <th>Store Owner<br></th>
                                    <th>Store<br></th>
                                    <th>Price<br></th>
                                    <th>Action<br></th>
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

@extends('layouts.app')

@section('script')
    <script type="text/javascript">
        var table;

        function reinitAct()
        {
            $('.editBItem').unbind();
            $('.editBItem').click(function(){
                blockUiStyled('<h4>Loading products details.</h4>');

                axios.get( $(this).data('action') )
                    .then(function (response) {
                        // handle success
                        $.unblockUI();

                        Swal.fire({
                            title: 'Edit product',
                            html: response.data.data,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-danger'
                            },
                            showCancelButton: true,
                            showCloseButton: true,
                            confirmButtonText: 'Update',
                            width: '70%',
                            allowOutsideClick: false,
                            preConfirm: ( pconfirm ) => {


                                $('.swal2-content').find('.is-invalid').removeClass('is-invalid');
                                $( $(document.getElementById('_form_errors')).parent() ).hide();

                                let fact = $('.swal2-content form')[0].action;
                                var formData = getFormData( $('.swal2-content form') );

                                blockUiStyled('<h4>Processing update.</h4>');

                                return axios.post( fact , formData , {
                                    headers: {
                                        'Content-Type': 'multipart/form-data'
                                    }
                                }).then(function (presponse) {

                                    $.unblockUI();
                                    //update log count
                                    if ( presponse.data.log ){
                                        for (let k=0; k<presponse.data.log.length; k++){
                                            logItems.push( presponse.data.log[k] );
                                        }
                                        calculateLog();
                                    }


                                    Swal.fire(
                                        'Success!',
                                        'Update all product done.',
                                        'success'
                                    ).then((result) => {
                                        loadData();
                                    });

                                }).catch(function (error) {
                                    $.unblockUI();

                                    if ( typeof error.response.data.errors != 'undefined'){
                                        var err_msg = '<ul>';
                                        $.each(error.response.data.errors, function(index, value) {
                                            let obj = document.getElementById( index ); // $('#'+index)
                                            let err = $(obj).parent().parent().find('.invalid-feedback');
                                            let msg = value.shift();

                                            $(err).empty().append( msg );
                                            $(obj).addClass('is-invalid')

                                            err_msg += '<li>' + msg + '</li>';
                                        });
                                        err_msg += '</ul>';

                                        let awin = document.getElementById('_form_errors');
                                        $(awin).empty().append(err_msg).parent();
                                        $( $(awin).parent() ).show().fadeOut(9000);
                                    }

                                    return false;
                                });
                            },
                        });

                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                        $.unblockUI();

                        Swal.fire(
                            'Info!',
                            'Looks stores do not have product scope or store reseting right now.',
                            'info'
                        ).then((result) => {
                            $('#_btnCreateProducts').click();
                        });
                    });

                return false;
            });
        }

        function loadData( created_from=null , product_only=false ){

            blockUiStyled('<h4>Loading stores information.</h4>');

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

                $.unblockUI();

                if ( stores.length == 0 ){

                    Swal.fire(
                        'Error!',
                        'Do not have store info, please check API log.',
                        'error'
                    );
                    return;
                }

                storeKeys = [];
                $.each(stores, function (key, value) {
                    storeKeys[key] = value.store_key
                });

                blockUiStyled('<h4>Loading products information.</h4>');

                axios.get(
                        '{{ route('businessCases.automatic_price_updating.products') }}',
                        {params: {store_keys: storeKeys}}
                    ).then(function (response) {
                        // handle success

                        $.unblockUI();

                        if ( typeof response.data.items == 'undefined' || response.data.items.length < 1 ){
                            // looks not items - show message to
                            return;
                        }

                        ptatable = $('#pdtable').dataTable().api();
                        ptatable.clear();

                        $.each( response.data.items , function( index, value ) {
                            let stor = stores.find(el => el.store_key === value.store_key);
                            value['product_store'] = stor;
                            ptatable.row.add( value );
                        });

                        ptatable.draw();
                        reinitAct();
                    })
                    .catch(function (error) {
                        // handle error
                        $('#_btnCreateProducts').click();
                    })

            }).catch(function (error) {
                // handle error

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

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            loadData();

            table = $('#pdtable').DataTable( {
                processing: true,
                serverSide: false,
                ordering: true,
                bLengthChange: false,
                data: items,
                dom: '<"row"<"col"bl><"col"><"col">><t><"row"<"col"i><"col"p>>',
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
                columnDefs: [
                    {
                        'targets': 0,
                        'checkboxes': {
                            'selectAll': true,
                            'selectRow': true,
                            'selectAllPages': false,
                            'selectCallback': function(nodes,selected){
                                // console.log( nodes, selected  );
                                product_selected = [];
                                table.rows().every(function(index, element ){
                                    var tnode = this.node();

                                    if ( $(tnode).find('input.dt-checkboxes').is(':checked') ){
                                        product_selected.push( $(tnode).find('input.dt-checkboxes').val() );
                                    }

                                });

                                $(".num_selected").empty();

                                if (product_selected.length){
                                    $(".num_selected").append( "Selected: " + product_selected.length );
                                }
                            },
                        }
                    }
                ],
                select: {
                    'style': 'multi',
                },
                columns: [
                    { data: null, render:
                            function(data, type, row, meta){
                                return '';
                            },orderable : false,  "searchable": false,
                    },
                    { data: null, render: function ( data, type, row, meta ){
                            let imgurl = (data.images[0])? data.images[0].http_path : '{{ asset('css/img/no_image_275x275.jpg') }}';
                            return '<img src="'+imgurl+'" style="max-width: 60px; max-height: 60px;">'
                        }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                let desc = (typeof data.description != 'undefined') ? data.description : '';
                                return data.name + '<br><small class="text-muted more" data-toggle="popover" data-trigger="hover" data-content="'+desc.escapeHTML()+'">'+desc.trunc(80)+'</small>';
                            }
                    },
                    { data: null, render: function ( data, type, row, meta ){
                            return (typeof data.u_sku != 'undefined') ? data.u_sku : '';
                        }
                    },
                    { data: null, render: function ( data, type, row, meta ){
                            let st = '';
                            if (data.product_store !== undefined) {


                                let imgName = data.product_store.cart_info.cart_name.toLowerCase().replace(/ /g, "_");
                                st += '<div style="float: left"><span class="cartImage circle-int ' + imgName + '"></span></div>';
                                st += '<div class="cartInfo">' +
                                        '<small><a href="' + data.product_store.url + '" target="_blank">' + data.product_store.url + '</a></small>' +
                                      '</div>';
                            }
                            return st;
                        }},
                    { data: null, render: function ( data, type, row, meta ){
                            return data.price;
                        },orderable : false,  "searchable": false,
                    },
                    { data: null, render:
                            function(data, type, row, meta){
                                return data.quantity;
                            },orderable : false,  "searchable": false,
                    },
                    { data: null, render:
                            function(data, type, row, meta){
                                return '<a href="javascript:void(0);"  class="text-success editBItem"' +
                                         'data-action="{{ route('businessCases.automatic_price_updating.edit') }}?store_key=' + data.store_key + '&id=' + data.id + '"' +
                                         'data-id="'+data.u_sku+'" ><i class="fas fa-edit"></i>' +
                                       '</a> ';
                            },orderable : false,  "searchable": false,
                    },

                ],
                "drawCallback": function( settings ) {
                    $('[data-toggle="popover"]').popover('hide');
                    $('[data-toggle="popover"]').popover({
                        html: true
                    });
                    // reinitActions();
                }
            } );

            $('#_btnCreateProducts').click(function(){

                axios.get( '{{ route('businessCases.automatic_price_updating.create') }}' )
                    .then(function (response) {
                        // handle success
                        Swal.fire({
                            title: 'Create product',
                            html: response.data.data,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                cancelButton: 'btn btn-danger'
                            },
                            showCancelButton: true,
                            showCloseButton: true,
                            confirmButtonText: 'Create',
                            width: '70%',
                            allowOutsideClick: false,
                            preConfirm: ( pconfirm ) => {

                                // console.log( pconfirm );

                                $('.swal2-content').find('.is-invalid').removeClass('is-invalid');
                                $( $(document.getElementById('_form_errors')).parent() ).hide();

                                let fact = $('.swal2-content form')[0].action;
                                var formData = getFormData( $('.swal2-content form') );

                                blockUiStyled('<h4>Processing new product and load info.</h4>');

                                return axios.post( fact , formData , {
                                    headers: {
                                        'Content-Type': 'multipart/form-data'
                                    }
                                }).then(function (presponse) {
                                    $.unblockUI();

                                    //update log count
                                    if ( presponse.data.log ){
                                        for (let k=0; k<presponse.data.log.length; k++){
                                            if (k < 2) {//skip product.info
                                                logItems.push(presponse.data.log[k]);
                                            }
                                        }
                                        calculateLog();
                                    }

                                    if (presponse.data.success) {
                                        Swal.fire(
                                            'OK!',
                                            'New product created succesfully! ',
                                            'success'
                                        ).then((result) => {
                                            loadData();
                                        });
                                    } else {
                                        Swal.fire(
                                            'ERROR!',
                                            presponse.data.errormessage,
                                            'error'
                                        );
                                    }

                                }).catch(function (error) {
                                    $.unblockUI();

                                    if ( typeof error.response != 'undefined' && typeof error.response.data.errors != 'undefined'){
                                        var err_msg = '<ul>';
                                        $.each(error.response.data.errors, function(index, value) {
                                            let obj = document.getElementById( index ); // $('#'+index)
                                            let err = $(obj).parent().parent().find('.invalid-feedback');
                                            let msg = value.shift();

                                            $(err).empty().append( msg );
                                            $(obj).addClass('is-invalid');

                                            err_msg += '<li>' + msg + '</li>';
                                        });
                                        err_msg += '</ul>';

                                        let awin = document.getElementById('_form_errors');
                                        $(awin).empty().append(err_msg).parent();
                                        $( $(awin).parent() ).show().fadeOut(9000);
                                    }

                                    return false;
                                });
                            },
                        });
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                        $.unblockUI();

                        Swal.fire(
                            'Error!',
                            'Failed create ' + name,
                            'error'
                        )
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
                    <div class="card-header">Automatic emails sending <span class="ajax_status"></span></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h1>Automatic managing online store product prices</h1>
                                <p>Example: Retrieve and update product prices on various sales channels</p>
                                <p>Providing clients with the possibility to update and synchronize prices on various shopping platforms and marketplaces automatically is one of the vital functions of multi-channel and repricing and price optimization software providers. With API2Cart <a href="https://docs.api2cart.com/post/interactive-docs?version=v1.1#/product/ProductList" target="_blank">product.list</a> and <a href="https://docs.api2cart.com/post/interactive-docs?version=v1.1#/product/ProductUpdate" target="_blank">product.update</a> methods you can work with price data efficiently!</p>
                                <p class="text-center"><img class="img-fluid" src="{{ asset('images/automatic-price-updating.jpg') }}" style="max-height: 300px;"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button class="btn btn-primary" id="_btnCreateProducts" data-action="{{ route('businessCases.automatic_price_updating.create') }}">Create new products</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col text-right api_log">
                                <a href="#" id="showApiLog" >Performed <span>0</span> requests with API2Cart. Click to see details...</a><br>
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="pdtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Image</th>
                                            <th>Name/Description</th>
                                            <th>SKU</th>
                                            <th>Stores</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
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
        </div>
    </div>
@endsection

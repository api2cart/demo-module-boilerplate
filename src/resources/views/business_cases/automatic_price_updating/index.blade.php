@extends('layouts.app')

@section('script')
    <script type="text/javascript">
        var table;

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // blockUiStyled('<h4>Loading stores information.</h4>');

            // loadData();

            table = $('#pdtable').DataTable( {
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
                order: [[1, 'desc']],
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
                        },orderable : false,  "searchable": false,
                    },
                    { data: null, render:
                            function(data, type, row, meta){
                                return '';
                            },orderable : false,  "searchable": false,
                    },
                    { data: null, render:
                            function(data, type, row, meta){
                                return '';
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
                        // console.log(response);

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

                                console.log( pconfirm );

                            },
                        });


                        //update log count
                        // if ( response.data.log ){
                        //     for (let k=0; k<response.data.log.length; k++){
                        //         logItems.push( response.data.log[k] );
                        //     }
                        //     calculateLog();
                        // }
                        // $.unblockUI();

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
                                <button class="btn btn-primary" id="_btnCreateProducts">Create new products</button>
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

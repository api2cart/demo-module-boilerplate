@extends('layouts.app')

@section('script')
    <script type="text/javascript">
        let items = new Array();


        function blockUiStyled(message){
            $.blockUI({
                message: message,
                css: {
                    border: 'none',
                    padding: '15px',
                    backgroundColor: '#000',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    opacity: .5,
                    color: '#fff',
                } });
        }


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

                let stores = response.data.data;

                for (let i=0; i<stores.length; i++){

                    blockUiStyled('<h3>Loading '+ stores[i].cart_id +' information.</h3>');

                    axios({
                        method: 'post',
                        url: '{{ route('products.list') }}/'+stores[i].store_key,
                        data: {
                            length: 10,
                            start: 0
                        }
                    }).then(function (rep) {

                        let orders = rep.data.data;

                        blockUiStyled('<h3>Adding '+ stores[i].cart_id +' product.</h3>');

                        for (let j=0; j<orders.length; j++){
                            items.push( orders[j] );
                        }

                         // console.log( rep.data );


                        var datatable = $( '#dtable' ).dataTable().api();

                        datatable.clear();
                        datatable.rows.add( items );
                        datatable.draw();

                        $.unblockUI();

                        $.growlUI('Notification', stores[i].cart_id + ' data loaded successfull!');
                        $('[data-toggle="popover"]').popover({
                            html: true
                        });

                    });


                }




            });
        }




        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            blockUiStyled('<h3>Loading stores information.</h3>');

            loadData();

            // $.unblockUI();

            // console.log( items );


            $('#dtable').DataTable( {
                processing: true,
                serverSide: false,
                // ordering: false,
                data: items,
                dom: 'B<lf<t>ip>',
                buttons: [
                    {
                        text: 'Reload',
                        action: function ( e, dt, node, config ) {

                            // $.blockUI({ message: '<h2>Loading All Orders from store. <br>It can takes some time, so please keep calm.</h2>' });
                            //
                            // dt.clear();
                            // dt.draw();
                            // dt.ajax.reload(function(){
                            //     $.unblockUI();
                            // });
                            loadData();

                        }
                    }
                ],
                columns: [
                    { data: null, render: function ( data, type, row, meta ){
                            let imgurl = (data.images[0])? data.images[0].http_path : '{{ asset('css/img/no_image_275x275.jpg') }}';
                            return '<img src="'+imgurl+'" style="max-width: 60px; max-height: 60px;">'
                        }, orderable : false
                    },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.name + '<br><small class="text-muted more" data-toggle="popover" data-content="'+data.description.escapeHTML()+'">'+data.description.trunc(60)+'</small>';
                            }
                    },
                    { data: null, render: function ( data, type, row, meta ){
                            return data.u_sku;
                        }},
                    { data: null, render: function ( data, type, row, meta ){
                            return data.stores_info.store_owner_info.owner+'<br><small>'+data.stores_info.store_owner_info.email+'</small>';
                     }},
                    { data: null, render: function ( data, type, row, meta ){
                            return '<a href="'+data.stores_info.multi_store_url+'">'+data.stores_info.multi_store_url+'</a><br>'+data.cart_info.cart_name+'<br><small>'+data.cart_info.cart_versions+'</small>';
                    }},
                    { data: null, render: 'price' },
                    {
                        data: null, render: function ( data, type, row, meta ){
                            return '<a href="#" aria-disabled="true" class="text-secondary disabled"><ion-icon name="open-outline"></ion-icon></a> ' +
                                '<a href="#" aria-disabled="true" class="text-success disabled"><ion-icon name="pencil-outline"></ion-icon></a> ' +
                                '<a href="#" aria-disabled="true" class="text-danger disabled"><ion-icon name="trash-outline"></ion-icon></a> ';
                        }, orderable : false
                    }
                ],
                "drawCallback": function( settings ) {
                    $('[data-toggle="popover"]').popover('hide');
                    $('[data-toggle="popover"]').popover({
                        html: true
                    });

                }
            } );

            $('#dtable').on( 'page.dt', function () {
                $('[data-toggle="popover"]').popover('hide');
            } );

        } );
    </script>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">Products</div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <table id="dtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name/Description</th>
                                    <th>SKU</th>
                                    <th>Store Owner</th>
                                    <th>Store</th>
                                    <th>Price</th>
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

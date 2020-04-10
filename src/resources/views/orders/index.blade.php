@extends('layouts.app')

@section('script')
    <script type="text/javascript">
        let items = new Array();



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

                        blockUiStyled('<h3>Adding '+ stores[i].url +' orders.</h3>');

                        for (let j=0; j<orders.length; j++){
                            orders[j].cart_id = stores[i];
                            items.push( orders[j] );
                        }

                        // console.log( rep.data );


                        var datatable = $( '#dtable' ).dataTable().api();

                        datatable.clear();
                        datatable.rows.add( items );
                        datatable.draw();

                        $.unblockUI();

                        $.growlUI('Notification', stores[i].url + ' data loaded successfull!');

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
                dom: '<"row"<"col"B><"col"l><"col"f>><t><"row"<"col"i><"col">p>',
                buttons: [
                    {
                        text: 'Reload',
                        action: function ( e, dt, node, config ) {

                            window.location.reload();

                        }
                    }
                ],
                initComplete: function () {
                    $('#dtable_filter input').focus();
                },
                columns: [
                    { data: null, render: 'order_id' },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return '<a href="'+data.cart_id.url+'">'+data.cart_id.url+'</a><br>'+
                                    '<small>'+data.cart_id.stores_info.store_owner_info.owner+'</small><br>'+
                                    '<small>'+data.cart_id.stores_info.store_owner_info.email+'</small>';
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
                            return '<a href="#" aria-disabled="true" class="text-secondary disabled"><ion-icon name="open-outline"></ion-icon></a> ' +
                                '<a href="#" aria-disabled="true" class="text-success disabled"><ion-icon name="pencil-outline"></ion-icon></a> ' +
                                '<a href="#" aria-disabled="true" class="text-danger disabled"><ion-icon name="trash-outline"></ion-icon></a> ';
                        }, orderable : false
                    }
                ]
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
                    <div class="card-header">Orders</div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <table id="dtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th>Id</th>
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

@extends('layouts.app')

@section('script')
    <script type="text/javascript">


        function loadData(){

            blockUiStyled('<h3>Loading stores information.</h3>');

            stores = [];


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

                var datatable = $( '#dtable' ).dataTable().api();

                datatable.clear();
                datatable.rows.add( stores );
                datatable.draw();

                $.unblockUI();

                reinitActions(); $.growlUI('Notification',  ' data loaded successfull!', 500 );

            });
        }



        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });



            loadData();
            // $.unblockUI();

            $('#dtable').DataTable( {
                processing: true,
                // serverSide: true,
                // ordering: false,
                data: stores,
                // dom: 'B<lf<t>ip>',
                dom: '<"row"<"col"B><"col"l><"col"f>><t><"row"<"col"i><"col">p>',
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
                columns: [
                    { data: null, render: function ( data, type, row, meta ){
                            return '<a href="'+data.url+'">'+data.url+'</a><br><small>'+data.store_key+'</small>';
                    } },
                    { data: null, render: function ( data, type, row, meta ){
                        let imgName = data.cart_info.cart_name.toLowerCase().replace(/ /g,"_");
                        return '<img class="cartImage" src="https://api2cart.com/wp-content/themes/api2cart/images/logos/'+imgName+'.png"><br>' +
                        data.cart_info.cart_name+'<br><small>'+data.cart_info.cart_versions+'</small>';
                    }  },
                    { data: null, render: function ( data, type, row, meta ){

                            return data.stores_info.store_owner_info.owner+'<br><small>'+data.stores_info.store_owner_info.email+'</small>';

                        }  },
                    {
                        data: null, render: function ( data, type, row, meta ){
                            return '<i class="far fa-file-alt"></i> ' +
                                '<i class="fas fa-edit"></i> ' +
                                '<a href="#"  class="text-danger deleteItem" data-id="'+data.id+'" data-name="'+data.url+'" data-action="/stores/'+data.store_key+'"><i class="fas fa-trash-alt"></i></a> ';
                        }, orderable : false
                    }
                ],
                "drawCallback": function( settings ) {
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
                    <div class="card-header">Stores <span class="ajax_status"></span></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col text-right api_log">
                                <a href="#" id="showApiLog" >Performed <span>0</span> requests with API2Cart. Click to see details...</a><br>
                            </div>
                        </div>
                        <table id="dtable" class="table table-bordered" style="width:100%">
                            <thead>
                            <tr>
                                <th>Store</th>
                                <th>Cart Type</th>
                                <th>Store Owner</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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

            blockUiStyled('<h3>Loading stores information.</h3>');

            items = [];


            return axios({
                method: 'post',
                url: '{{ route('stores.list') }}',
                data: {
                    length: 10,
                    start: 0
                }
            }).then(function (response) {

                items = response.data.data;

                var datatable = $( '#dtable' ).dataTable().api();

                datatable.clear();
                datatable.rows.add( items );
                datatable.draw();

                $.unblockUI();
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
                data: items,
                dom: 'B<lf<t>ip>',
                buttons: [
                    {
                        text: 'Reload',
                        action: function ( e, dt, node, config ) {
                            loadData();
                        }
                    }
                ],
                columns: [
                    { data: null, render: function ( data, type, row, meta ){
                            return '<a href="'+data.url+'">'+data.url+'</a><br><small>'+data.store_key+'</small>';
                    } },
                    { data: null, render: function ( data, type, row, meta ){
                        return data.cart_info.cart_name+'<br><small>'+data.cart_info.cart_versions+'</small>';
                    }  },
                    { data: null, render: function ( data, type, row, meta ){
                            return data.stores_info.store_owner_info.owner+'<br><small>'+data.stores_info.store_owner_info.email+'</small>';
                        }  },
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
    <div class="container">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">Stores</div>

                    <div class="card-body">

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

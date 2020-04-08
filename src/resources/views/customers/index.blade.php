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
                        url: '{{ route('customers.list') }}/'+stores[i].store_key,
                        data: {
                            length: 10,
                            start: 0
                        }
                    }).then(function (rep) {

                        let orders = rep.data.data;

                        blockUiStyled('<h3>Adding '+ stores[i].cart_id +' customers.</h3>');

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

            $.unblockUI();

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
                    { data: null, render: 'id' },
                    { data: null, render: 'cart_id' },
                    { data: null, render: 'email' },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.first_name +' '+ data.last_name;
                            }
                    },
                    {
                        data: null, render: function ( data, type, row, meta ){
                            return '<a href="/customers/details/'+ data.id +'" aria-disabled="true" class="btn btn-success btn-sm disabled"><svg class="bi bi-pencil-square" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">\n' +
                                '  <path d="M15.502 1.94a.5.5 0 010 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 01.707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 00-.121.196l-.805 2.414a.25.25 0 00.316.316l2.414-.805a.5.5 0 00.196-.12l6.813-6.814z"/>\n' +
                                '  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 002.5 15h11a1.5 1.5 0 001.5-1.5v-6a.5.5 0 00-1 0v6a.5.5 0 01-.5.5h-11a.5.5 0 01-.5-.5v-11a.5.5 0 01.5-.5H9a.5.5 0 000-1H2.5A1.5 1.5 0 001 2.5v11z" clip-rule="evenodd"/>\n' +
                                '</svg></a> '+
                                '<a href="/customers/details/'+ data.id +'" aria-disabled="true" class="btn btn-primary btn-sm disabled"><svg class="bi bi-info-square" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">\n' +
                                '  <path fill-rule="evenodd" d="M14 1H2a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V2a1 1 0 00-1-1zM2 0a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V2a2 2 0 00-2-2H2z" clip-rule="evenodd"/>\n' +
                                '  <path d="M8.93 6.588l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588z"/>\n' +
                                '  <circle cx="8" cy="4.5" r="1"/>\n' +
                                '</svg></a>';
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
                    <div class="card-header">Customers</div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <table id="dtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Store</th>
                                    <th>Email</th>
                                    <th>Name</th>
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

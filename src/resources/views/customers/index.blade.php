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
                        url: '{{ route('customers.list') }}/'+stores[i].store_key,
                        data: {
                            length: 10,
                            start: 0
                        }
                    }).then(function (rep) {

                        let orders = rep.data.data;

                        blockUiStyled('<h4>Adding '+ stores[i].url +' customers.</h4>');

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
            var store = getUniqueStore();

            yadcf.init( table , [
                {
                    column_number: 3,
                    select_type: 'select2',
                    data: names,
                    select_type_options: { width: '200px' }
                },
                {
                    column_number: 1,
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
                var name = item.first_name +' '+ item.last_name;
                if (!~uniqueItem.indexOf(name)) {
                    uniqueItem.push(name);
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
                bLengthChange: false,
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
                columns: [
                    { data: null, render: 'id' },
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
                    { data: null, render: 'email' },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                return data.first_name +' '+ data.last_name;
                            }
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
                    <div class="card-header">Customers <span class="ajax_status"></span>
                        <span class="float-right"><a target="_blank" href="https://docs.api2cart.com/post/interactive-docs?version=v1.1#operations-tag-customer">Read Customers API methods</a></span>
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
                                    <th>Store</th>
                                    <th>Email</th>
                                    <th>Name</th>
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

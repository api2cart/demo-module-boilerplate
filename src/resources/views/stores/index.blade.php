@extends('layouts.app')

@section('script')
    <script type="text/javascript">
        let selectItems = [];

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

                if ( !stores.length ){
                    Swal.fire(
                        'Info',
                        'Do not have store info, please check API log or Add stores.',
                        'info'
                    )
                }

                var datatable = $( '#dtable' ).dataTable().api();

                datatable.clear();
                datatable.rows.add( stores );
                datatable.draw();

                $.unblockUI();

                reinitActions();
                $.growlUI('Notification',  ' data loaded successfull!', 500 );
                initFilters();

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


        function addStore()
        {
            let action = "{{ route('stores.create') }}";

            axios.get( action )
                .then(function (response) {
                    // handle success
                    // console.log(response);

                    selectItems = response.data.item;

                    Swal.fire({
                        title: 'Add new Store',
                        html: response.data.data,
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger'
                        },
                        showCancelButton: true,
                        showCloseButton: true,
                        confirmButtonText: 'Create',
                        width: '70%',
                        allowOutsideClick: false,
                        preConfirm: ( pconfirm ) => {

                            $('.swal2-content').find('.is-invalid').removeClass('is-invalid');

                            let fact = $('.swal2-content form')[0].action;
                            var formData = getFormData( $('.swal2-content form') );


                            return axios.post( fact , formData , {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            })
                                .then(function (presponse) {

                                    //TODO: store added


                                    return true;
                                })
                                .catch(function (error) {
                                    // console.log( error );

                                    if ( typeof error.response.data.errors != 'undefined'){

                                        $.each(error.response.data.errors, function(index, value) {
                                            let obj = $( document.getElementById(index) );
                                            let err = $(obj).parent().parent().find('.invalid-feedback');
                                            $(err).empty().append( value.shift() );
                                            $(obj).addClass('is-invalid')
                                        });

                                    }


                                    return false;
                                });


                        },
                    });



                    $('#cart_id').change(function(e){

                        let selected = this.value;



                        let item = Object.values( selectItems ).find( obj =>{ return obj.cart_id === selected } );
                        $('#addItemFields').empty();

                        axios.get( 'stores/fields/' + item.cart_id ).then(function (cfresponse) {
                            $('#addItemFields').append( cfresponse.data );
                        });



                        // console.log(  );

                        // for (let value of Object.values( item.params )) {
                        //     // console.log( value );
                        //
                        //     $('#addItemFields').append(
                        //     '<div class="form-group row">\n' +
                        //     '                <label for="field.'+value+'" class="col-4 col-form-label">'+value+'</label>\n' +
                        //     '                <div class="col-8">\n' +
                        //     '                    <input class="form-control" id="field.'+value+'" name="field['+value+']" required="required" >\n' +
                        //     '                    <div class="invalid-feedback"></div>\n' +
                        //     '                </div>\n' +
                        //     '            </div>'
                        //     );
                        // }


                    });



                    //update log count
                    if ( response.data.log ){
                        for (let k=0; k<response.data.log.length; k++){
                            logItems.push( response.data.log[k] );
                        }
                        calculateLog();
                    }
                    $.unblockUI();

                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                    $.unblockUI();

                    Swal.fire(
                        'Error!',
                        'Failed info ' + id,
                        'error'
                    )

                });
        }


        function initFilters()
        {
            var names = getUniqueName();
            var owner = getUniqueOwner();
            var store = getUniqueStore();


            yadcf.init( table , [
                {
                    column_number: 1,
                    select_type: 'select2',
                    data: names,
                    select_type_options: { width: '200px' }
                },
                {
                    column_number: 2,
                    select_type: 'select2',
                    data: owner,
                    select_type_options: { width: '200px' }
                },
                {
                    column_number: 0,
                    select_type: 'select2',
                    data: store,
                    select_type_options: { width: '200px' }
                },

            ]);

        }


        function getUniqueName()
        {
            var uniqueItem = [];
            stores.filter(function(item){
                let name = item.cart_info.cart_name
                if (!~uniqueItem.indexOf(name)) {
                    uniqueItem.push(name);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniqueOwner()
        {
            var uniqueItem = [];
            stores.filter(function(item){
                let owner = (item.stores_info.store_owner_info.owner) ? item.stores_info.store_owner_info.owner : '';
                if (!~uniqueItem.indexOf(owner)) {
                    uniqueItem.push(owner);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getUniqueStore()
        {
            var uniqueItem = [];
            stores.filter(function(item){
                let url = (item.url) ? item.url : '';
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



            loadData();
            // $.unblockUI();

            table = $('#dtable').DataTable( {
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
                    },
                    {
                        text: 'Add Store',
                        action: function ( e, dt, node, config ) {

                            addStore();

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

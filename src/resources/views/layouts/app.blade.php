<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-MSLGM6D');</script>
    <!-- End Google Tag Manager -->


    <script type="text/javascript" src="{{ asset('js/axios.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.blockUI.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/fontawesome/js/all.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/sweetalert2/dist/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/fileinput/js/fileinput.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/fileinput/js/plugins/piexif.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/fileinput/themes/fas/theme.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jqdcheckboxes/js/dataTables.checkboxes.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/yadcf/jquery.dataTables.yadcf.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jui/jquery-ui.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/select2/js/select2.min.js') }}"></script>

    <script>
        let items = new Array();
        let logItems = new Array();
        let logTable;
        let stores;

        axios.defaults.headers.common = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN' : '{{ csrf_token() }}'
        };
        axios.defaults.timeout = 5*60*1000;

        let numberOfAjaxCAllPending = 0;

        // Add a request interceptor
        axios.interceptors.request.use(function (config) {
            numberOfAjaxCAllPending++;
            // show loader

            if ( $('.ajax_status').length ){
                $('.ajax_status').empty().append('<img src="{{ asset('css/img/loading.gif') }}" style="max-height: 24px;"> Loading....');
            }

            return config;
        }, function (error) {

            return Promise.reject(error);
        });

        // Add a response interceptor
        axios.interceptors.response.use(function (response) {
            numberOfAjaxCAllPending--;
            // console.log("------------  Ajax pending", numberOfAjaxCAllPending);

            if (numberOfAjaxCAllPending == 0) {
                //hide loader
                if ( $('.ajax_status').length ){
                    $('.ajax_status').empty();
                }
            } else {
                if ( $('.ajax_status').length ){
                    $('.ajax_status').empty().append('<img src="{{ asset('css/img/loading.gif') }}" style="max-height: 24px;"> Loading....');
                }
            }
            return response;
        }, function (error) {

            numberOfAjaxCAllPending--;
            if (numberOfAjaxCAllPending == 0) {
                //hide loader
                if ( $('.ajax_status').length ){
                    $('.ajax_status').empty();
                }
            }
            return Promise.reject(error);
        });


        String.prototype.trunc = String.prototype.trunc ||
            function(n){
                return (this.length > n) ? this.substr(0, n-1) + '&hellip;' : this;
            };
        String.prototype.escapeHTML = function() {
            return this.replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function calculateLog()
        {
            if ( $('.api_log').length ){
                $('.api_log span').empty().append( logItems.length );
                reinitApiLogTable();
            }
        }

        function reinitActions()
        {
            var datatable = $( '#dtable' ).dataTable().api();

            $('[data-toggle="popover"]').popover('hide');

            $('.deleteItem').unbind();
            $('.deleteItem').click(function(){
                let name = $(this).data('name');
                let action = $(this).data('action');
                let id = $(this).data('id');

                let drow = $(this).parent().parent();

                datatable.$('tr.selected').removeClass('selected');
                $(drow).addClass('selected');

                Swal.fire({
                    title: 'Are you sure?',
                    html: "<strong>" + name + "</strong> be deleted! <br>You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {

                        datatable.row('.selected').remove().draw(false);


                        axios({
                            method: 'delete',
                            url: action
                        }).then(function (response) {

                            Swal.fire(
                                'Deleted!',
                                'Your '+name+' has been deleted.',
                                'success'
                            );

                            //update log count
                            if ( response.data.log ){
                                for (let k=0; k<response.data.log.length; k++){
                                    logItems.push( response.data.log[k] );
                                }
                                calculateLog();
                            }
                        });
                    }
                });
                return false;
            });


            $('.editItem').unbind();
            $('.editItem').click(function(){

                if ( $(this).hasClass('disabled') ) return false;

                let name = $(this).data('name');
                let action = $(this).data('action');
                let deleteimages = $(this).data('deleteimages');
                let id = $(this).data('id');

                let trdata = datatable.row( $(this).parents('tr') );

                // console.log( trdata );

                blockUiStyled('<h4>Loading '+name+' details</h4>');

                axios.get( action )
                    .then(function (response) {
                        // handle success
                        // console.log(response);

                        Swal.fire({
                            title: 'Edit '+name,
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
                                var imagefile = document.querySelector('#images');
                                var formData = getFormData( $('.swal2-content form') );

                                if ( typeof rows_selected !== 'undefined' ){
                                    for (var n=0; n<rows_selected.length; n++){
                                        formData.append("selected_items["+n+"]", rows_selected[n]);
                                    }
                                }

                                if ( imagefile && imagefile.files.length ){
                                    for (var n=0; n<imagefile.files.length; n++){
                                        formData.append("image["+n+"]", imagefile.files[n]);
                                    }
                                }


                                return axios.post( fact , formData , {
                                    headers: {
                                        'Content-Type': 'multipart/form-data'
                                    }
                                })
                                    .then(function (presponse) {

                                        //update log count
                                        if ( presponse.data.log ){
                                            for (let k=0; k<presponse.data.log.length; k++){
                                                logItems.push( presponse.data.log[k] );
                                            }
                                            calculateLog();
                                        }

                                        // update 1 item
                                        if ( typeof presponse.data.item != 'undefined' ){

                                            let nitem = presponse.data.item;
                                            if ( nitem == null ){
                                                $.unblockUI();
                                                Swal.fire(
                                                    'Error!',
                                                    'Failed edit ' + name + '<br>Please check API log.',
                                                    'error'
                                                );
                                                return true;
                                            }

                                            if ( typeof trdata.data().cart_id != 'undefined' ){
                                                nitem.cart_id = trdata.data().cart_id;
                                            }
                                            if ( typeof trdata.data().parent_name != 'undefined' ){
                                                nitem.parent_name = trdata.data().parent_name;
                                            }
                                            trdata.data( nitem ).draw();

                                        }
                                        else if ( typeof presponse.data.items != 'undefined' ){

                                            $.each(presponse.data.items, function(index, value) {

                                                table.rows().every(function(){
                                                    var tobj  = this;
                                                    var tnode = tobj.node();
                                                    var tdata = tobj.data();

                                                    if ( $(tnode).find('input.dt-checkboxes').is(':checked') ){

                                                        // table node
                                                        if ( $(tnode).find('input.dt-checkboxes').val() == value.selected_item ){

                                                            if ( typeof tdata.cart_id != 'undefined' ){
                                                                value.cart_id = tdata.cart_id;
                                                            }
                                                            if ( typeof tdata.parent_name != 'undefined' ){
                                                                value.parent_name = tdata.parent_name;
                                                            }
                                                            tobj.data( value ).draw();
                                                        }
                                                    }
                                                });
                                            });

                                            table.rows().columns().checkboxes.deselectAll();

                                        } else {
                                            $.unblockUI();
                                            Swal.fire(
                                                'Error!',
                                                'Failed edit ' + name + '<br>Please check API log.',
                                                'error'
                                            );
                                        }

                                        return true;
                                    })
                                    .catch(function (error) {
                                        console.log(error);

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

                        let eitem = response.data.item;
                        let initialPreview = [];
                        let initialPreviewConfig = [];

                        if ( typeof eitem.images !== 'undefined' && eitem.images.length ){

                            $.each( eitem.images , function( index, value ) {
                                initialPreview.push("<img class='file-preview-image kv-preview-data' src='"+ value.http_path +"'>");
                                initialPreviewConfig.push({
                                    url : deleteimages,
                                    key : value.id,
                                    downloadUrl: false
                                });
                            });

                            $("#images").fileinput({
                                showUpload: false,
                                theme: "fas",
                                dropZoneEnabled: false,
                                maxFileCount: 10,
                                maxFileSize: 100,
                                mainClass: "input-group-lg",
                                overwriteInitial: false,
                                initialPreview: initialPreview,
                                initialPreviewConfig: initialPreviewConfig
                            });

                        }

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
                            'Failed edit ' + name,
                            'error'
                        )

                    });

                return false;
            });


            $('.infoItem').unbind();
            $('.infoItem').click(function(){
                let name = $(this).data('name');
                let action = $(this).data('action');
                let id = $(this).data('id');

                let trdata = datatable.row( $(this).parents('tr') );

                axios.get( action )
                    .then(function (response) {
                        // handle success
                        // console.log(response);

                        Swal.fire({
                            title: 'Info '+name,
                            html: response.data.data,
                            showCloseButton: true,
                            width: '70%',
                            allowOutsideClick: false,
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

                return false;
            });

            $('.productsItem').unbind();
            $('.productsItem').click(function(){
                let name = $(this).data('name');
                let action = $(this).data('action');
                let id = $(this).data('id');

                let trdata = datatable.row( $(this).parents('tr') );

                axios.get( action )
                    .then(function (response) {
                        // handle success
                        // console.log(response);

                        Swal.fire({
                            title: 'Info '+name,
                            html: response.data.data,
                            showCloseButton: true,
                            width: '70%',
                            allowOutsideClick: false,
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

                return false;
            });
        }

        $(document).ready(function() {
            // 'use strict';

            logTable = $( '#logtable' ).DataTable({
                serverSide: false,
                data: logItems,
                order: [[ 0, "desc" ]],
                columns: [
                    { data: null, render: 'created_at' },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                if ( data.store_id ) {
                                    return  stores.find(el => el.store_key === data.store_id)['url'];
                                }
                                return '';
                            }
                    },
                    { data: null, render: 'action' },
                    { data: null, render:
                            function ( data, type, row, meta ){
                                let mr = '<div><div class="dolessmore">';
                                $.each(data.params, function( index, value ) {

                                    if ( typeof value === 'object' ){

                                        $.each( value, function( i, v ) {
                                            if ( typeof v === 'object'){
                                                mr += '<small><strong>'+ index +'->'+ i + '</strong> : ' + JSON.stringify( v ) +'</small><br>';
                                            } else {
                                                mr += '<small><strong>'+ index +'->'+ i + '</strong> : ' + v +'</small><br>';
                                            }
                                        });

                                    } else {
                                        if ( data.code == 0 )  mr += '<small><strong>'+ index + '</strong> : ' + value.trunc(40) +'</small><br>';
                                    }

                                });
                                mr += '</div><span class="dolessmoreShow">Show more</span></div>'
                                return mr;
                            }
                    },
                    {
                        data: null, render: function (data, type, row, meta) {
                            if (data.code == 0) {
                                return data.code;
                            } else {

                                return data.code + "<br>" + data.params.msg;

                            }
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return '<button type="button" name="showLog" class="btn btn-outline-info logInfo" style="line-height: 0.9rem; padding: 0.5rem;" id="' + data.id + '" ' +
                                     'data-log="<pre>' + JSON.stringify(JSON.parse(data.response), null, 2).replace(/\"/g, '\'') + '</pre>">' +
                                       '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">' +
                                          '<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>' +
                                          '<path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>' +
                                       '</svg>' +
                                   '</button>'
                        }
                    }

                ],
                "drawCallback": function( settings ) {
                    // console.log( settings );
                    $('.dolessmoreShow').unbind();
                    $('.dolessmoreShow').click(function(){
                        var obj = $(this).parent().find('.dolessmore').first();

                        if ( $(obj).css('height') == '40px' ){
                            $(obj).css('height','auto')
                            $(this).text('Show less');
                        } else {
                            $(obj).css('height','40px')
                            $(this).text('Show more');
                        }

                        // console.log( $(obj).css('max-height') );
                    });
                }
            });


            $('#showApiLog').click(function(){
                reinitApiLogTable();
                $('#staticBackdrop').modal('show');
                return false;
            });

            $("#logtable").on('click', '.logInfo', function () {
                var LogId = $(this).attr("id");
                $('#configId').val(LogId);
                $('#logResponse').html($(this).data('log'));
                $('#modalLogInfo .modal-title').html("Detail Response Info #" + LogId);
                $('#modalLogInfo').modal('show');
                return false;
            });

            $("#modalLogInfo").on('hide.bs.modal', function(){
                setTimeout(function () {
                    $(document.body).addClass('modal-open');//fix main modal scroll
                }, 400);
            });

        });

        function displayProperties(val, out )
        {

            if (typeof val === 'object') {

                $.each( val , function( index, value ) {

                    if ( typeof value === 'object'){

                        // console.log( value );
                        // out += displayProperties( value, out );

                    } else {
                        out += '<small><strong>'+ index + '</strong> : ' + value +'</small><br>';
                    }

                });

                return out;

            }

            return out;

        }

        function reinitApiLogTable()
        {
            var datatable = $( '#logtable' ).dataTable().api();
            datatable.clear();
            datatable.rows.add( logItems );
            datatable.draw();

            var codes  = getLogUniqueCode();
            var method = getLogUniqueMethod();
            var storeu  = getLogUniqueStores();

            var myTable = $('#logtable').DataTable();


            yadcf.init( myTable , [
                {
                    column_number: 2,
                    select_type: 'select2',
                    data: method,
                    select_type_options: { width: '200px' },
                    style_class: "subjectFilter"
                },
                {
                    column_number: 1,
                    select_type: 'select2',
                    data: storeu,
                    select_type_options: { width: '200px' },
                    style_class: "subjectFilter"
                },
                {
                    column_number: 4,
                    select_type: 'select2',
                    data: codes,
                    style_class: "subjectFilter"
                    // select_type_options: { width: '200px' }
                }
            ]);

            $('.dolessmoreShow').unbind();
            $('.dolessmoreShow').click(function(){
                var obj = $(this).parent().find('.dolessmore').first();

                if ( $(obj).css('height') == '40px' ){
                    $(obj).css('height','auto')
                    $(this).text('Show less');
                } else {
                    $(obj).css('height','40px')
                    $(this).text('Show more');
                }

            });

        }

        function getLogUniqueCode()
        {
            var uniqueItem = [];
            logItems.filter(function(item){
                if (!~uniqueItem.indexOf(item.code)) {
                    uniqueItem.push(item.code);
                    return item;
                }
            });
            return uniqueItem;
        }
        function getLogUniqueStores()
        {
            var uniqueItem = [];
            stores.filter(function(item){
                if (!~uniqueItem.indexOf(item.url)) {
                    uniqueItem.push(item.url);
                    return item;
                }
            });
            return uniqueItem;
        }
        function getLogUniqueMethod()
        {
            var uniqueItem = [];
            logItems.filter(function(item){
                if (!~uniqueItem.indexOf(item.action)) {
                    uniqueItem.push(item.action);
                    return item;
                }
            });
            return uniqueItem;
        }

        function getFormData($form) {
            var unindexed_array = $form.serializeArray();
            var formdata = new FormData();
            $.map(unindexed_array, function (n, i) {
                formdata.append(n['name'], n['value']);
            });
            return formdata;
        }

    </script>
    <script type="text/javascript" >
        function blockUiStyled(message){
            $.blockUI({
                message: message,
                baseZ: 9300,
                css: {
                    border: 'none',
                    padding: '15px',
                    fontSize:'12px',
                    backgroundColor: '#fff',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    opacity: .95,
                    color: '#000',
                    margin:         0,
                    width:          '70%',
                    top:            '40%',
                    left:           '15%',
                },
                overlayCSS:  {
                    backgroundColor: '#000',
                    opacity:         0.85,
                    cursor:          'wait'
                },
            });
        }
    </script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="{{ asset('js/sweetalert2/dist/sweetalert2.min.css') }}"/>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/dataTables.bootstrap4.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('js/select2/css/select2.css') }}"  />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}"/>

{{--    <link rel="stylesheet" type="text/css" href="{{ asset('css/buttons.dataTables.min.css') }}"/>--}}

    <link rel="stylesheet" type="text/css" href="{{ asset('js/fontawesome/css/all.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('js/fileinput/css/fileinput.min.css') }}" media="all" />
    <link rel="stylesheet" type="text/css" href="{{ asset('js/jqdcheckboxes/css/dataTables.checkboxes.css') }}"  />
    <link rel="stylesheet" type="text/css" href="{{ asset('js/yadcf/jquery.dataTables.yadcf.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('js/jui/jquery-ui.min.css') }}"/>

    @yield('script')

</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MSLGM6D"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

    <div class="container">
        <div class="row justify-content-center">
            <div class="fusion-contact-info cont-info">
                <span>Got questions? <a href="/contact-us" style=" top: -1px; position: relative;">Contact Us</a><span class="dot-space">,</span></span>
            </div>
            <div class="fusion-contact-info">
                <span>Call Us <a href="tel:18002240976" style=" top: -1px; position: relative;">1-800-224-0976</a> or write <a href="mailto:manager@api2cart.com" style=" top: -1px; position: relative;">manager@api2cart.com</a></span>
            </div>
        </div>
    </div>

    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm _magMenu" style="background-color: cadetblue !important; padding: 5px;margin-left: 15px;margin-right: 15px;margin-bottom: 5px;border-radius: 5px;">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}"> Demo
                </a>
                <div><small>In this demo account, all data will reset every hour</small></div>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('users.edit', Auth::user()->id) }}">
                                        Profile
                                    </a>

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <main class="" style="margin-top: 15px;">
            @include('parts.messages')

            @yield('content')
        </main>
    </div>

    @include('parts.footer')

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog full_modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">API2Cart Requests</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="table-responsive">
                        <table id="logtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                            <thead>
                            <tr>
                                <th>Time<br></th>
                                <th>Store<br></th>
                                <th>Method<br></th>
                                <th>Params<br></th>
                                <th>Response Code<br></th>
                                <th></th>
                            </tr>
                            </thead>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal request info -->
    <div class="modal fade" id="modalLogInfo" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">API2Cart Requests Info</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span> Response</span>
                        </div>
                        <div class="panel-body" id="logResponse" style="overflow: auto;max-height: 75vh;background-color: #444;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

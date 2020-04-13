<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script type="text/javascript" src="{{ asset('js/axios.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <script>
        let logItems = new Array();
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

        $(document).ready(function() {

            $( '#logtable' ).dataTable({
                serverSide: false,
                data: logItems,
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
                                let mr = '';
                                $.each(data.params, function( index, value ) {
                                    mr += '<small><strong>'+ index + '</strong> : ' + value +'</small><br>';
                                });
                                return mr;
                            }
                    },
                    { data: null, render: 'code' },
                ]
            });

            $('#showApiLog').click(function(){

                // console.log( logItems );
                // console.log( stores );

                reinitApiLogTable();


                $('#staticBackdrop').modal('show');
                return false;
            });
        });

        function reinitApiLogTable()
        {
            var datatable = $( '#logtable' ).dataTable().api();
            datatable.clear();
            datatable.rows.add( logItems );
            datatable.draw();
        }

    </script>
    <script type="text/javascript" src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.blockUI.js') }}"></script>
    <script type="text/javascript" >
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
                    left: '0px;',
                    width: '100%'
                } });
        }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.esm.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">


    <link rel="stylesheet" type="text/css" href="{{ asset('css/dataTables.bootstrap4.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/buttons.dataTables.min.css') }}"/>

    <script type="text/javascript" src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>



    @yield('script')

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
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
        <main class="py-4">
            @include('parts.messages')

            @yield('content')
        </main>
    </div>

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
                                <th>Time</th>
                                <th>Store</th>
                                <th>Method</th>
                                <th>Params</th>
                                <th>Response Code</th>
                            </tr>
                            </thead>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header"></div>

                    <div class="card-body">
                        <p><strong>Please setup mailing settings</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

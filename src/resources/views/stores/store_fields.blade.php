
@php
    $required     = collect( $store['params']['required'] );
    $custom     = collect( $store['params']['additional'] );
@endphp

<div class="form-group row">
    <label for="field.store_url" class="col-4 col-form-label">store_url</label>
    <div class="col-8">
        <input type="text" class="form-control" id="field.store_url" name="field[store_url]" value="">
        <small id="store_url.Help" class="form-text text-muted">A web address of a store that you would like to connect to API2Cart</small>
        <div class="invalid-feedback"></div>
    </div>
</div>

@if ($store['db'])
    <div class="form-group row">
        <label for="field.store_key" class="col-4 col-form-label">store_key</label>
        <div class="col-8">
            <input type="text" class="form-control" id="field.store_key" name="field[store_key]" value="">
            <small id="store_key.Help" class="form-text text-muted">Set this parameter if bridge is already uploaded to store</small>
            <div class="invalid-feedback"></div>
        </div>
    </div>
@endif

@if ($required->count() == 1)
    @foreach( collect($required->first())->whereNotIn('name',['cart_id']) as $k=>$v )
        <div class="form-group row">
            <label for="field.{{$v['name']}}" class="col-4 col-form-label">{{ $v['name'] }}</label>
            <div class="col-8">
                <input type="text" class="form-control" id="field.{{$v['name']}}" name="field[{{ $v['name'] }}]" value="">
                <small id="{{$v['name']}}.Help" class="form-text text-muted">{{ $v['description'] }}</small>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    @endforeach
@else
    <div id="multiCred" class="form-group row">
        <div class="col-md-2 mb-3 nav-pills" style="padding-left: 10px">
            <ul class="nav md-pills pills-secondary d-flex flex-column">
                @foreach($required as $key => $req)
                    @php
                        $active = $key === 0 ? 'active' : '';
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link {{$active}}" data-toggle="tab" href="#panel{{$key}}" role="tab">Credentials set{{$key}}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="col-md-10 mb-9 tab-block">

            <div class="tab-content pt-0">

                @foreach($required as $paramsSetKey => $require)
                    @php
                        $active = $paramsSetKey === 0 ? 'active' : '';
                    @endphp
                    <div class="tab-pane fade in show {{$active}}" id="panel{{$paramsSetKey}}" role="tabpanel">
                        @foreach(collect($require)->whereNotIn('name',['cart_id']) as $k => $v )
                            <div class="form-group row">
                                <label for="field.{{$v['name']}}" class="col-4 col-form-label">{{ $v['name'] }}</label>
                                <div class="col-8">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="field.multicred.{{$paramsSetKey}}.{{$v['name']}}"
                                        name="field[multicred][{{$paramsSetKey}}][{{$v['name']}}]"
                                        value=""
                                    >
                                    <small id="{{$v['name']}}.Help" class="form-text text-muted">{{ $v['description'] }}</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if( count($custom) > 0 )

    <div class="form-group row">
        <label for="verify" class="col-4 col-form-label"></label>
        <div class="col-8">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="verify" name="field[verify]" value="false">
                <label class="custom-control-label" for="verify">Skip verification</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label for="verify" class="col-4 col-form-label"></label>
        <div class="col-8">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="use_additional" name="use_additional" data-toggle="collapse" data-target="#collapseExample" >
                <label class="custom-control-label" for="use_additional">Use additional params</label>
            </div>
        </div>
    </div>



    <div class="collapse" id="collapseExample">
        @foreach( $custom->whereNotIn('name',['verify']) as $item )
            <div class="form-group row">
                <label for="custom.{{$item['name']}}" class="col-4 col-form-label">{{ $item['name'] }}</label>
                <div class="col-8">
                    <input type="text" class="form-control" id="custom.{{$item['name']}}" name="custom[{{ $item['name'] }}]" value="">
                    <small id="{{$item['name']}}.Help" class="form-text text-muted">{{ $item['description'] }}</small>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        @endforeach
    </div>


@endif


@php
    $required     = collect( $store['params']['required'][0] );
    $custom     = collect( $store['params']['additional'] );
@endphp

@foreach( $required->whereNotIn('name',['cart_id']) as $k=>$v )
<div class="form-group row">
    <label for="field.{{$v['name']}}" class="col-4 col-form-label">{{ $v['name'] }}</label>
    <div class="col-8">
        <input type="text" class="form-control" id="field.{{$v['name']}}" name="field[{{ $v['name'] }}]" value="">
        <small id="emailHelp" class="form-text text-muted">{{ $v['description'] }}</small>
        <div class="invalid-feedback"></div>
    </div>
</div>
@endforeach

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

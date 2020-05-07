
@php $filtered = collect( array_diff($store['params'],['cart_id','verify','ftp_host','ftp_user','ftp_password','ftp_port','ftp_store_dir']) ); @endphp

@foreach( $filtered as $k=>$v )
<div class="form-group row">
    <label for="{{ $v }}" class="col-4 col-form-label">{{ $v }}</label>
    <div class="col-8">
        <input type="text" class="form-control" id="field.{{$v}}" name="field[{{ $v }}]" value="">
        <div class="invalid-feedback"></div>
    </div>
</div>
@endforeach
@if( in_array('verify', $store['params']) )
    <div class="form-group row">
        <label for="verify" class="col-4 col-form-label"></label>
        <div class="col-8">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="verify" name="field[verify]" value="false">
                <label class="custom-control-label" for="verify">Skip verification</label>
            </div>
        </div>
    </div>
@endif

@if( in_array('ftp_host', $store['params']) && in_array('ftp_store_dir', $store['params']) )


<div class="custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" id="upload_bridge" name="upload_bridge" data-toggle="collapse" data-target="#collapseExample">
    <label class="custom-control-label" for="upload_bridge">Please upload bridge to my store</label>
</div>

<div class="collapse" id="collapseExample">

    <div class="form-group row">
        <label for="ftp_host" class="col-4 col-form-label">ftp_host</label>
        <div class="col-8">
            <input type="text" class="form-control" id="ftp_host" name="ftp_host" value="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="ftp_port" class="col-4 col-form-label">ftp_port</label>
        <div class="col-8">
            <input type="text" class="form-control" id="ftp_port" name="ftp_port" value="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="ftp_user" class="col-4 col-form-label">ftp_user</label>
        <div class="col-8">
            <input type="text" class="form-control" id="ftp_user" name="ftp_user" value="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="ftp_password" class="col-4 col-form-label">ftp_password</label>
        <div class="col-8">
            <input type="text" class="form-control" id="ftp_password" name="ftp_password" value="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="form-group row">
        <label for="ftp_store_dir" class="col-4 col-form-label">ftp_store_dir</label>
        <div class="col-8">
            <input type="text" class="form-control" id="ftp_store_dir" name="ftp_store_dir" value="">
            <div class="invalid-feedback"></div>
        </div>
    </div>

</div>

@endif
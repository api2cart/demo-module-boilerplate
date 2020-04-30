@if(isset($store_id))
    {!! Form::open(['url' => route('stores.update', [$store_id]) ]) !!}
@else
    {!! Form::open(['url' => route('stores.store') ]) !!}
@endif



<br>
<div class="row text-left">
    <div class="col">
        <div class="form-group row">
            <label for="name" class="col-4 col-form-label">Store Type</label>
            <div class="col-8">
                <select class="custom-select" id="cart_id" name="cart_id" required >
                    <option selected disabled value="">Choose your cart...</option>
                    @foreach($stores as $store)
                    <option value="{!! $store['cart_id'] !!}">{{ $store['cart_name'] }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div id="addItemFields">
        </div>

    </div>
</div>


{!! Form::close() !!}
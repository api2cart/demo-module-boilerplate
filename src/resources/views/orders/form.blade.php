@if(isset($order_id))
    {!! Form::open(['url' => route('orders.update', [$order_id]) ]) !!}
@else
    {!! Form::open(['url' => route('orders.store') ]) !!}
@endif



<br>
<div class="row text-left">
    <div class="col">

        <div class="form-group row">
            <label for="name" class="col-4 col-form-label">Store Type</label>
            <div class="col-8">
                <select class="custom-select" id="cart_id" name="cart_id" required >
                    <option selected disabled value="">Choose your cart...</option>
                    @foreach($carts as $item)
                        <option value="{!! $item['store_key'] !!}">{{ $item['cart_id'] }} [ {{ $item['url'] }} ] </option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="form-group row">
            <label for="customer_id" class="col-4 col-form-label">Customer</label>
            <div class="col-8">
                <select class="custom-select" id="customer_id" name="customer_id" required disabled>
                    <option selected disabled value="">Choose customer...</option>
                </select>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="form-group row">
            <label for="" class="col-4 col-form-label">Products</label>
            <div class="col-8" >

                <div class="row" id="productsList" style="max-height: 300px; overflow-y: scroll;">

{{--                    <label class="col-lg-6">--}}
{{--                        <input type="checkbox" name="demo[]" class="card-input-element d-none" value="demo1">--}}
{{--                        <div class="card card-body bg-light d-flex ">--}}
{{--                            <h6>Product name <span class="badge badge-secondary">$10</span></h6>--}}
{{--                            <small>--}}
{{--                                Quantity: <input type="number" min="0" step="1" value="0">--}}
{{--                            </small>--}}
{{--                        </div>--}}
{{--                    </label>--}}


                </div>


            </div>
        </div>


        <div id="addItemFields">
        </div>

    </div>
</div>


{!! Form::close() !!}
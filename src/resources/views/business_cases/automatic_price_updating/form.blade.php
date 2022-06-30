@php
    $faker = Faker\Factory::create();
@endphp
@if(isset($product))
    {!! Form::open(['url' => route('businessCases.automatic_price_updating.update'), 'id' => 'form', 'method' => 'put' ]) !!}
@else
    {!! Form::open(['url' => route('businessCases.automatic_price_updating.store'),'id' => 'form' ]) !!}
@endif

<div class="alert alert-danger" role="alert" style="display: none;">
    <div id="_form_errors" class="text-left"></div>
</div>

@if (isset($carts))
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
@elseif (isset($storeKey))
    <input type="hidden" name="cart_id" value="{{ $storeKey }}" required>
    <input type="hidden" name="product_id" value="{{ $productId }}" required>
@endif

<div class="row">
    <div class="col-12">
        <div class="form-group row">
            <label for="name" class="col-4 col-form-label">Name</label>
            <div class="col-8">
                <input type="text" required class="form-control" id="name" name="name" value="{{ (isset($product['name'])) ? $product['name'] : $faker->sentence() }}">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>

@if ($isCreate)
<div class="row">
    <div class="col-12">
        <div class="form-group row">
            <label for="name" class="col-4 col-form-label">SKU</label>
            <div class="col-8">
                <input type="text" required class="form-control" id="sku" name="sku" value="{{ (isset($product['u_sku'])) ? $product['u_sku'] : $faker->uuid }}">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="form-group row">
            <label for="description" class="col-4 col-form-label">Description</label>
            <div class="col-8">
                <textarea class="form-control" required rows="6" id="description" name="description">{!! (isset($product['description'])) ? $product['description'] : $faker->text() !!}</textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group row">
                <label for="price" class="col-4 col-form-label">Price</label>
                <div class="col-8">
                    <input type="number" class="form-control" required id="price" name="price" value="{{ ( isset($product['price']) ) ? $product['price'] : $faker->randomFloat(2,1,100) }}" step="0.01"  >
                    <div class="invalid-feedback"></div>
                </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group row">
            <label for="price" class="col-4 col-form-label">Quantity</label>
            <div class="col-8">
                <input type="number" class="form-control" required id="quantity" name="quantity" value="{{ ( isset($product['quantity']) ) ? $product['quantity'] : $faker->randomNumber(2) }}" step="1" >
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>


{!! Form::close() !!}

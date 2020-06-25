@php
    $faker = Faker\Factory::create();
@endphp
@if(isset($product_id))
    {!! Form::open(['url' => route('businessCases.automatic_price_updating.update', [$store_id, $product_id]) ]) !!}
@else
    {!! Form::open(['url' => route('businessCases.automatic_price_updating.create') ]) !!}
@endif

<div class="alert alert-danger" role="alert" style="display: none;">
    <div id="_form_errors" class="text-left"></div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group row">
            <label for="name" class="col-4 col-form-label">Name</label>
            <div class="col-8">
                <input type="text" class="form-control" id="name" name="name" value="{{ (isset($product['name'])) ? $product['name'] : $faker->sentence() }}">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group row">
            <label for="name" class="col-4 col-form-label">SKU</label>
            <div class="col-8">
                <input type="text" class="form-control" id="name" name="name" value="{{ (isset($product['sku'])) ? $product['sku'] : $faker->uuid }}">
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group row">
            <label for="description" class="col-4 col-form-label">Description</label>
            <div class="col-8">
                <textarea class="form-control" rows="6" id="description" name="description">{!! (isset($product['description'])) ? $product['description'] : $faker->text() !!}</textarea>
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
                    <input type="number" class="form-control" id="price" name="price" value="{{ ( isset($product['price']) ) ? $product['price'] : $faker->randomFloat(2,1,100) }}" step="0.01"  >
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
                <input type="number" class="form-control" id="quantity" name="quantity" value="{{ ( isset($product['quantity']) ) ? $product['quantity'] : $faker->randomNumber(2) }}" step="1" >
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>


{!! Form::close() !!}

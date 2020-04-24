@if($product_id)
    {!! Form::open(['url' => route('products.update', [$store_id, $product_id]) ]) !!}
@else
@endif
<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">General</a>
        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-variant" role="tab" aria-controls="nav-variant" aria-selected="false">Variants</a>
        <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-options" role="tab" aria-controls="nav-options" aria-selected="false">Options</a>
        <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-child" role="tab" aria-controls="nav-child" aria-selected="false">Child Items</a>
    </div>
</nav>
<div class="tab-content" id="nav-tabContent" style="padding-top: 5px; text-align: left;">
    <div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-home-tab">
        <div class="row">
            <div class="col-8">
                <div class="form-group row">
                    <label for="name" class="col-4 col-form-label">Name</label>
                    <div class="col-8">
                        <input type="text" class="form-control" id="name" name="name" value="{{ (isset($product['name'])) ? $product['name'] : '' }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                Variants Count:
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <div class="form-group row">
                    <label for="description" class="col-4 col-form-label">Description</label>
                    <div class="col-8">
                        <textarea class="form-control" rows="6" id="description" name="description">{!! (isset($product['description'])) ? $product['description'] : '' !!}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                Options Count: {{ (isset($product['product_options'][0]['option_items'])) ? count($product['product_options'][0]['option_items']) : '' }}
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <div class="form-group row">
                    <label for="price" class="col-4 col-form-label">Price</label>
                    <div class="col-8">
                        <input type="number" class="form-control" id="price" name="price" value="{{ (isset($product['price'])) ? $product['price'] : '' }}" step="0.01">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                Child Items Count:
            </div>
        </div>

        <div class="row">

            <div class="col">
                <div class="form-group row">
                    <label for="images" class="col-2 col-form-label">Images</label>
                    <div class="col-10">
                        <input id="images" name="images[]" type="file" class="file" data-preview-file-type="text" multiple>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="nav-variant" role="tabpanel" aria-labelledby="nav-profile-tab">
        <br><br>
        <h5>Coming soon...</h5>
        <br><br>
    </div>

    <div class="tab-pane fade" id="nav-options" role="tabpanel" aria-labelledby="nav-contact-tab">
        <br><br>
        <h5>Coming soon...</h5>
        <br><br>
    </div>

    <div class="tab-pane fade" id="nav-child" role="tabpanel" aria-labelledby="nav-contact-tab">
        <br><br>
        <h5>Coming soon...</h5>
        <br><br>
    </div>
</div>
{!! Form::close() !!}

@if($category_id)
    {!! Form::open(['url' => route('categories.update', [$store_id, $category_id]) ]) !!}
@else
@endif
<br>
<div class="row text-left">
    <div class="col">
        <div class="form-group row">
            <label for="name" class="col-4 col-form-label">Name</label>
            <div class="col-8">
                <input type="text" class="form-control" id="name" name="name" value="{{ (isset($category['name'])) ? $category['name'] : '' }}">
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="form-group row">
            <label for="description" class="col-4 col-form-label">Description</label>
            <div class="col-8">
                <textarea class="form-control" id="description" name="description">{{ (isset($category['description'])) ? $category['description'] : '' }}</textarea>
                <div class="invalid-feedback"></div>
            </div>
        </div>
    </div>
</div>
{!! Form::close() !!}

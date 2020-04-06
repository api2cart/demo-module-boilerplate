<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
    {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ('' == 'required') ? ['class' => 'form-control', 'required' => 'required'] : ['class' => 'form-control']) !!}
    {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('api2cart_key') ? 'has-error' : ''}}">
    {!! Form::label('api2cart_key', 'API Key', ['class' => 'control-label']) !!}
    {!! Form::text('api2cart_key', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete'=>'off'] ) !!}
    {!! $errors->first('api2cart_key', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('password') ? 'has-error' : ''}}">
    {!! Form::label('password', 'Password', ['class' => 'control-label']) !!}
    {!! Form::password('password', ['class' => 'form-control', 'autocomplete'=>'off']) !!}
    {!! $errors->first('password', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : ''}}">
    {!! Form::label('password_confirmation', 'Confirm Password', ['class' => 'control-label']) !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control','autocomplete'=>'off']) !!}
    {!! $errors->first('password_confirmation', '<p class="help-block">:message</p>') !!}
</div>


<div class="form-group">
    {!! Form::submit($formMode === 'edit' ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

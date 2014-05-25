@extends('larapress::layouts.login')

@section('content')
    @include('larapress::partials.captcha')

    {{ Form::open() }}
        <div class="form-group">
            {{ Form::label('email', trans('larapress::forms.Email address')) }}
            {{ Form::text('email', '', array('class' => 'form-control', 'placeholder' => trans('larapress::forms.Enter email'))) }}
        </div>
        {{ HTML::linkRoute('larapress.home.login.get',
            trans('larapress::forms.Abort and go back'), array(), array('class' => 'btn btn-lg btn-default btn-block')) }}
        {{ Form::submit(trans('larapress::forms.Reset my password'), array('class' => 'btn btn-lg btn-default btn-block btn-dark')) }}
    {{ Form::close() }}
@stop

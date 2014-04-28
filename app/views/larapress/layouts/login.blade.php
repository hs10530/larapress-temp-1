@extends('larapress.layouts.master')

@section('head.extension')
    {{ HTML::style('larapress/assets/css/pages/home/login.css') }}
@stop

@section('body')
    <div id="wrapper">
        <div id="login-box">
            <img src="{{ asset('larapress/assets/png/logo.png') }}">

            {{-- Messages --}}
            @if ( Session::has('error') )
                <div class="alert alert-danger alert-dismissable fade in">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong>@lang('messages.Error')!</strong>
                    @lang('messages.' . Session::get('error'))
                </div>
            @endif

            @yield('content')
        </div>
        <p id="copyright">
            &copy; {{{ $now->year }}} <a href="http://kontakt.hettiger.com" target="_blank">Martin Hettiger</a> |
            Powered by <a href="http://laravel.com" target="_blank">Laravel</a>
        </p>
    </div>
@stop

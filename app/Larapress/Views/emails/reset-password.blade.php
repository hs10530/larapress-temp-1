@lang('larapress::email.Hello!')


@lang('larapress::email.Someone, hopefully you, has requested a password reset for your ' . $cms_name . ' cms account.')

@lang('larapress::email.By clicking on the following link you\'ll recceive a second email containing a new password:')


{{ $url }}


@lang('larapress::email.If you didn\'t request a password reset for your account, you can safely ignore this email.')

@lang('larapress::email.Have a nice day!')

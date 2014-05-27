<?php namespace Larapress\Tests\Services;

use Artisan;
use Input;
use Larapress\Tests\TestCase;
use Log;
use Mail;
use Narrator;
use Sentry;

class NarratorTest extends TestCase
{

    public $log_message;

    /*
    |--------------------------------------------------------------------------
    | Narrator::sendMail() Tests
    |--------------------------------------------------------------------------
    |
    | Here is where you can test the Narrator::sendMail() method
    |
    */

    /**
     * @expectedException \Larapress\Exceptions\MailException
     * @expectedExceptionMessage foo
     */
    public function test_can_throw_a_mail_exception_with_error_message()
    {
        Narrator::setTo(array('address' => 'example@domain.com', 'name' => null));
        Narrator::setSubject('Test');
        Narrator::setData(array());
        Narrator::setView(array());
        Narrator::setMailErrorMessage('foo');

        Mail::shouldReceive('send')->once()->andReturn(false);

        Narrator::sendMail();
    }

    /**
     * @expectedException \Larapress\Exceptions\MailException
     * @expectedExceptionMessage Cannot send message without a sender address
     */
    public function test_can_throw_a_mail_exception_warning_about_missing_sender_address()
    {
        Narrator::setTo(array('address' => 'example@domain.com', 'name' => null));
        Narrator::setSubject('Test');
        Narrator::setData(array());
        Narrator::setView(array());
        Narrator::setMailErrorMessage('foo');

        Mail::shouldReceive('send')->once()->andThrow(
            'Swift_TransportException',
            'Cannot send message without a sender address'
        );

        Narrator::sendMail();
    }

    /**
     * @expectedException \Larapress\Exceptions\MailException
     * @expectedExceptionMessage foo
     */
    public function test_can_fall_back_to_the_provided_error_message_on_unknown_swift_transport_exception_messages()
    {
        Narrator::setTo(array('address' => 'example@domain.com', 'name' => null));
        Narrator::setSubject('Test');
        Narrator::setData(array());
        Narrator::setView(array());
        Narrator::setMailErrorMessage('foo');

        Mail::shouldReceive('send')->once()->andThrow(
            'Swift_TransportException',
            'bar'
        );

        Narrator::sendMail();
    }

    public function test_can_send_an_email()
    {
        Narrator::setTo(array('address' => 'example@domain.com', 'name' => null));
        Narrator::setSubject('Test');
        Narrator::setData(array());
        Narrator::setView(array());
        Narrator::setMailErrorMessage('');

        Log::listen(function($level, $message, $context)
        {
            $this->log_message = $message;
        });

        Narrator::sendMail();
        $this->assertEquals('Pretending to mail message to: example@domain.com', $this->log_message);
    }

    /*
    |--------------------------------------------------------------------------
    | Narrator::resetPassword() Tests
    |--------------------------------------------------------------------------
    |
    | Here is where you can test the Narrator::resetPassword() method
    |
    */

    /**
     * @expectedException \Cartalyst\Sentry\Users\UserNotFoundException
     */
    public function test_can_throw_a_user_not_found_exception()
    {
        Artisan::call('larapress:install');
        Input::merge(array('email' => 'example@domain.tld'));

        Narrator::resetPassword();
    }

    public function test_can_send_a_reset_password_email()
    {
        Artisan::call('larapress:install');
        Input::merge(array('email' => 'admin@example.com'));

        Log::listen(function($level, $message, $context)
        {
            $this->log_message = $message;
        });

        Narrator::resetPassword();

        $this->assertEquals('Pretending to mail message to: admin@example.com', $this->log_message);
    }

    /*
    |--------------------------------------------------------------------------
    | Narrator::sendNewPassword() Tests
    |--------------------------------------------------------------------------
    |
    | Here is where you can test the Narrator::sendNewPassword() method
    |
    */

    /**
     * @expectedException \Cartalyst\Sentry\Users\UserNotFoundException
     */
    public function test_can_throw_a_user_not_found_exception_when_trying_to_send_a_new_password()
    {
        Artisan::call('larapress:install');

        Narrator::sendNewPassword(2, 'foo');
    }

    /**
     * @expectedException \Larapress\Exceptions\PasswordResetCodeInvalidException
     */
    public function test_can_throw_a_password_reset_code_invalid_exception()
    {
        Artisan::call('larapress:install');

        Narrator::sendNewPassword(1, 'foo');
    }

    public function test_can_send_the_new_password()
    {
        Artisan::call('larapress:install');
        $user = Sentry::findUserById(1);
        $reset_code = $user->getResetPasswordCode();

        Log::listen(function($level, $message, $context)
        {
            $this->log_message = $message;
        });

        Narrator::sendNewPassword(1, $reset_code);

        $this->assertEquals('Pretending to mail message to: admin@example.com', $this->log_message);
    }

}

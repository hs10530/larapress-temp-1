<?php namespace Larapress\Interfaces;

use Cartalyst\Sentry\Users\UserNotFoundException;
use Input;
use Larapress\Exceptions\MailException;
use Larapress\Exceptions\PasswordResetCodeInvalidException;
use Larapress\Exceptions\PasswordResetFailedException;

interface NarratorInterface {

    /**
     * @return void
     */
    public function __construct();

    /**
     * Data for the view/-s of your email
     *
     * @param array|object $data The data you want to pass to the view
     * @return void
     */
    public function setData($data);

    /**
     * The addressor for the email to send
     *
     * @param array $from From details: 'address' and 'name' (Provide strings)
     * @return void
     */
    public function setFrom($from);

    /**
     * This will be the exception message if sending fails
     *
     * @param string $mailErrorMessage The error message
     * @return void
     */
    public function setMailErrorMessage($mailErrorMessage);

    /**
     * The email subject
     *
     * @param string $subject The translated email subject
     * @return void
     */
    public function setSubject($subject);

    /**
     * The destination address for your mail
     *
     * @param array $to To details: 'address' and 'name' (Provide strings)
     * @return void
     */
    public function setTo($to);

    /**
     * The view/-s for your email
     *
     * @param array|string $view The view you want to use (Further information can be found in the laravel docs)
     * @return void
     */
    public function setView($view);

    /**
     * Send a simple email
     *
     * For more complex emails you might write another method
     *
     * @throws MailException Throws an exception containing further information as message
     * @return bool Returns true on success
     */
    public function sendMail();

    /**
     * Request an account reset
     *
     * This will generate a reset password code for the given user and send it via email to him.
     *
     * @param Input|null $input Passing Input::all() can be omitted
     * @throws UserNotFoundException Throws a UserNotFoundException if Sentry cannot find the given user.
     * @throws MailException Throws an exception containing further information as message
     * @return void
     */
    public function resetPassword($input);

    /**
     * Attempt to reset a user and send him a new password
     *
     * This will unsuspend a user and give him a new password.
     *
     * @param int $id The user id
     * @param string $reset_code The password reset code
     * @throws PasswordResetFailedException Throws an exception without further information on failure
     * @throws PasswordResetCodeInvalidException Throws an exception without further information on failure
     * @throws MailException Throws an exception containing further information as message
     * @throws UserNotFoundException Throws an exception without further information on failure
     * @return void
     */
    public function sendNewPassword($id, $reset_code);

}

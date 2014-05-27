<?php namespace Larapress\Services;

use Cartalyst\Sentry\Users\Eloquent\User;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Config;
use Input;
use Lang;
use Larapress\Exceptions\MailException;
use Larapress\Exceptions\PasswordResetCodeInvalidException;
use Larapress\Exceptions\PasswordResetFailedException;
use Larapress\Interfaces\NarratorInterface;
use Mail;
use Sentry;
use Swift_TransportException;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class Narrator implements NarratorInterface
{
    private $cmsName;
    private $view;
    private $to;
    private $from;
    private $subject;
    private $data;
    private $mailErrorMessage;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        $from = array(
            'address' => Config::get('larapress.email.from.address'),
            'name' => Config::get('larapress.email.from.name'),
        );

        $this->setFrom($from);
        $this->setCmsName($cms_name = Config::get('larapress.names.cms'));
    }

    /**
     * Check if a given variable contains a valid mail recipient
     *
     * @param mixed $var
     * @throws InvalidArgumentException
     * @return void
     */
    protected function validateMailRecipientData($var)
    {
        if ( ! is_array($var) )
        {
            throw new InvalidArgumentException;
        }
        elseif ( ! array_key_exists('address', $var) or ! array_key_exists('name', $var) )
        {
            throw new InvalidArgumentException;
        }
    }

    /**
     * Set the cms name
     *
     * @param string $cmsName
     */
    protected function setCmsName($cmsName)
    {
        $this->cmsName = $cmsName;
    }

    /**
     * Data for the view/-s of your email
     *
     * @param array|object $data The data you want to pass to the view
     * @return void
     */
    public function setData($data)
    {
        if ( ! is_array($data) and ! is_object($data) )
        {
            throw new InvalidArgumentException;
        }

        $this->data = $data;
    }

    /**
     * The addressor for the email to send
     *
     * @param array $from From details: 'address' and 'name' (Provide strings)
     * @return void
     */
    public function setFrom($from)
    {
        $this->validateMailRecipientData($from);

        $this->from = $from;
    }

    /**
     * This will be the exception message if sending fails
     *
     * @param string $mailErrorMessage The error message
     * @return void
     */
    public function setMailErrorMessage($mailErrorMessage)
    {
        if ( ! is_string($mailErrorMessage) )
        {
            throw new InvalidArgumentException;
        }

        $this->mailErrorMessage = $mailErrorMessage;
    }

    /**
     * The email subject
     *
     * @param string $subject The translated email subject
     * @return void
     */
    public function setSubject($subject)
    {
        if ( ! is_string($subject) )
        {
            throw new InvalidArgumentException;
        }

        $this->subject = $subject;
    }

    /**
     * The destination address for your mail
     *
     * @param array $to To details: 'address' and 'name' (Provide strings)
     * @return void
     */
    public function setTo($to)
    {
        $this->validateMailRecipientData($to);

        $this->to = $to;
    }

    /**
     * The view/-s for your email
     *
     * @param array|string $view The view you want to use (Further information can be found in the laravel docs)
     * @return void
     */
    public function setView($view)
    {
        if ( ! is_array($view) and ! is_string($view) )
        {
            throw new InvalidArgumentException;
        }

        $this->view = $view;
    }

    /**
     * Handle transport exception
     *
     * @param Swift_TransportException $e
     * @throws MailException Throws a friendly exception message
     */
    protected function handleTransportExceptions($e)
    {
        switch ( $e->getMessage() )
        {
            case 'Cannot send message without a sender address':
                throw new MailException($e->getMessage());
            default:
                throw new MailException($this->mailErrorMessage);
        }
    }

    /**
     * Send a simple email
     *
     * For more complex emails you might write another method
     *
     * @throws MailException Throws an exception containing further information as message
     * @return bool|void Returns true on success
     */
    public function sendMail()
    {
        try
        {
            $result = Mail::send($this->view, $this->data,
                function ($message) {
                    $message->from($this->from['address'], $this->from['name']);
                    $message->to($this->to['address'], $this->to['name'])->subject($this->subject);
                }
            );

            if ( ! $result ) {
                throw new MailException($this->mailErrorMessage);
            }

            return true;
        }
        catch (Swift_TransportException $e)
        {
            $this->handleTransportExceptions($e);
        }
    }

    /**
     * Prepare an email for account reset requests
     *
     * @param Input $input
     * @param User $user
     * @param string $reset_code
     */
    protected function prepareResetRequestMailData($input, $user, $reset_code)
    {
        $to = array(
            'address' => $input['email'],
            'name' => $user['first_name'] . ' ' . $user['last_name']
        );

        $data = array(
            'cms_name' => $this->cmsName,
            'url' => route('larapress.home.send.new.password.get', array($user['id'], $reset_code)),
        );

        $this->setTo($to);
        $this->setSubject($this->cmsName . ' | ' . Lang::get('larapress::email.Password Reset!'));
        $this->setData($data);
        $this->setView(array('text' => 'larapress::emails.reset-password'));
    }

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
    public function resetPassword($input = null)
    {
        $input = $input ? : Input::all();
        $user = Sentry::findUserByLogin($input['email']);
        $reset_code = $user->getResetPasswordCode();

        $this->prepareResetRequestMailData($input, $user, $reset_code);
        $this->setMailErrorMessage('Sending the email containing the reset key failed. ' .
            'Please try again later or contact the administrator.');

        $this->sendMail();
    }

    /**
     * Unsuspend the user and give him a new password
     *
     * @param User $user
     * @param int $id The user id
     * @param string $reset_code The password reset code
     * @throws PasswordResetFailedException
     * @return string Returns the new password on success
     */
    protected function unsuspendUserAndResetPassword($user, $id, $reset_code){
        $throttle = Sentry::findThrottlerByUserId($id);
        $throttle->unsuspend();

        $new_password = str_random(16);

        if ($user->attemptResetPassword($reset_code, $new_password))
        {
            return $new_password;
        }
        else
        {
            throw new PasswordResetFailedException;
        }
    }

    /**
     * Attempt to reset a user
     *
     * Initiate the account reset process
     *
     * @param int $id The user id
     * @param string $reset_code The password reset code
     * @throws PasswordResetFailedException Throws an exception without further information on failure
     * @throws PasswordResetCodeInvalidException Throws an exception without further information on failure
     * @return string Returns the new password on success
     */
    protected function attemptToReset($id, $reset_code)
    {
        $user = Sentry::findUserById($id);

        if ($user->checkResetPasswordCode($reset_code))
        {
            return $this->unsuspendUserAndResetPassword($user, $id, $reset_code);
        }
        else
        {
            throw new PasswordResetCodeInvalidException;
        }
    }

    /**
     * Prepare an email for account reset results
     *
     * @param User $user
     * @param int $id The user id
     * @param string $reset_code
     * @throws PasswordResetCodeInvalidException
     * @throws PasswordResetFailedException
     */
    protected function prepareResetResultMailData($user, $id, $reset_code)
    {
        $to = array(
            'address' => $user['email'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
        );

        $this->setTo($to);
        $this->setSubject($this->cmsName . ' | ' . Lang::get('larapress::email.Password Reset!'));
        $this->setData(array('new_password' => $this->attemptToReset($id, $reset_code)));
        $this->setView(array('text' => 'larapress::emails.new-password'));
    }

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
    public function sendNewPassword($id, $reset_code)
    {
        $user = Sentry::findUserById($id);

        $this->prepareResetResultMailData($user, $id, $reset_code);
        $this->setMailErrorMessage('Sending the email containing the new password failed. ' .
            'Please try again later or contact the administrator.');

        $this->sendMail();
    }

}

<?php

namespace App\Mail;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
	/**
	 * @var string
	 */
	private string $adminMailFrom;
	
	/**
	 * @var string
	 */
	private string $adminMailTo;
	
	/**
	 * @var MailerInterface
	 */
	private MailerInterface $mailer;
	
	/**
	 * Mailer constructor.
	 * @param  string           $adminMailFrom
	 * @param  string           $adminMailTo
	 * @param  MailerInterface  $mailer
	 */
	public function __construct(string $adminMailFrom, string $adminMailTo, MailerInterface $mailer)
	{
		$this->adminMailFrom = $adminMailFrom;
		$this->adminMailTo   = $adminMailTo;
		$this->mailer        = $mailer;
	}
	
	/**
	 * @param  string|array  $to
	 * @param  string        $subject
	 * @param  string        $template
	 * @param  array         $variables
	 * @return TemplatedEmail
	 * @throws TransportExceptionInterface
	 */
	public function sendTwigEmail($to, string $subject, string $template, array $variables): TemplatedEmail
	{
		$email = (new TemplatedEmail())
			->from($this->adminMailFrom)
			->subject($subject)
			->htmlTemplate($template)
			->context($variables)
		;
		
		if (is_array($to)) {
			foreach ($to as $recipient) {
				$email->to($recipient);
			}
		} else {
			$email->to($to);
		}
		
		$this->mailer->send($email);
		
		return $email;
	}
	
	/**
	 * @param  User  $user
	 * @return TemplatedEmail
	 * @throws TransportExceptionInterface
	 */
	public function sendWelcomeMessageAndActivationInstructions(User $user): TemplatedEmail
	{
		$email = (new TemplatedEmail())
			->to($user->getEmail())
			->from($this->adminMailFrom)
			->subject('Welcome to Photography Website!')
			->htmlTemplate('emailTemplates/registerSuccess.html.twig')
			->context(['user' => $user])
		;
		
		$this->mailer->send($email);
		
		return $email;
	}
	
	/**
	 * @param  User  $user
	 * @return TemplatedEmail
	 * @throws TransportExceptionInterface
	 */
	public function sendNewEmailConfirmationToken(User $user): TemplatedEmail
	{
		$email = (new TemplatedEmail())
			->to($user->getEmail())
			->from($this->adminMailFrom)
			->subject('New account activation code request for MicroPost App.')
			->htmlTemplate('emailTemplates/newEmailConfirmationToken.html.twig')
			->context(['user' => $user])
		;
		
		$this->mailer->send($email);
		
		return $email;
	}
	
	/**
	 * @param  User  $user
	 * @return TemplatedEmail
	 * @throws TransportExceptionInterface
	 */
	public function sendEmailChangeNotificationToNewEmail(User $user): TemplatedEmail
	{
		$email = (new TemplatedEmail())
			->to($user->getEmail())
			->from($this->adminMailFrom)
			->subject('Confirm your new email on Photography Blog')
			->htmlTemplate('emailTemplates/confirmEmail.html.twig')
			->context(['user' => $user])
		;
		
		$this->mailer->send($email);
		
		return $email;
	}
	
	/**
	 * @param  User  $user
	 * @return TemplatedEmail
	 * @throws TransportExceptionInterface
	 */
	public function sendEmailChangeNotificationToOldEmail(User $user): TemplatedEmail
	{
		$email = (new TemplatedEmail())
			->to($user->getOldEmail())
			->from($this->adminMailFrom)
			->subject('Notice of email change')
			->htmlTemplate('emailTemplates/emailChangedSuccessNotification.html.twig')
			->context(['user' => $user])
		;
		
		$this->mailer->send($email);
		
		return $email;
	}
	
	/**
	 * @param  User  $user
	 * @return TemplatedEmail
	 * @throws TransportExceptionInterface
	 */
	public function sendEmailChangeConfirmationToken(User $user): TemplatedEmail
	{
		$email = (new TemplatedEmail())
			->to($user->getEmail())
			->from($this->adminMailFrom)
			->subject('Email change request verification')
			->htmlTemplate('emailTemplates/requestEmailChange.html.twig')
			->context(['user' => $user])
		;
		
		$this->mailer->send($email);
		
		return $email;
	}
	
	/**
	 * @param  User  $user
	 * @return TemplatedEmail
	 * @throws TransportExceptionInterface
	 */
	public function sendPasswordResetToken(User $user): TemplatedEmail
	{
		$email = (new TemplatedEmail())
			->to($user->getEmail())
			->from($this->adminMailFrom)
			->subject('Password reset request for Photography Blog')
			->htmlTemplate('emailTemplates/requestPasswordChange.html.twig')
			->context(['user' => $user])
		;
		
		$this->mailer->send($email);
		
		return $email;
	}
}
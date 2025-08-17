<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomEmailVerificationNotification extends Notification
{
    use Queueable;

    /**
     * The callback that should be used to create the verify email URL.
     *
     * @var (\Closure(mixed, string): string)|null
     */
    public static $createUrlCallback;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('🔐 Verify Your Email - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for registering with our SAW Employee Evaluation System!')
            ->line('To get started, please verify your email address by clicking the button below.')
            ->action('✅ Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in ' . config('auth.verification.expire', 60) . ' minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Best regards, ' . config('app.name') . ' Team')
            ->markdown('emails.custom-notification', [
                'subject' => 'Email Verification Required',
                'message' => 'Please verify your email address to access the SAW system.',
                'action_text' => 'Verify Email',
                'action_url' => $verificationUrl,
                'recipient_name' => $notifiable->name
            ]);
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'email_verification',
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'verification_url' => $this->verificationUrl($notifiable),
        ];
    }
}

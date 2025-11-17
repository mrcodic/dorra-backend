<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public array $channels = ['mail', 'database'])
    {
        $this->afterCommit();
        $this->delay(now()->addSeconds(4));
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New customer signed up')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("A new user has registered: {$this->user->name} ({$this->user->email}).")
            ->action('View User', route("users.show", $this->user->id))
            ->line('Thanks!');
    }

    public function toArray(object $notifiable): array
    {
        $name  = trim($this->user->name);
        $email = $this->user->email;

        return [
            'title'   => 'New user signed up',
            'body'    => $name
                ? "{$name} ({$email}) has just registered."
                : "{$email} has just registered.",
            'url'     => route('users.show', $this->user->id),
        ];
    }

}

<?php

namespace App\Notifications;

use App\Models\IncidentGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentGroupAnalyzedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public IncidentGroup $group)
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
        return (new MailMessage)
            ->subject("Incident Report: [{$this->group->highest_severity}] Group: {$this->group->title}")
            ->line('A security incident group has been analyzed.')
            ->line("**MITRE:** {$this->group->mitre_id}")
            ->line("**Host:** {$this->group->host}")
            ->line("**Severity:** {$this->group->highest_severity}")
            ->line("**Total Occurrences:** {$this->group->total_occurrences}")
            ->line("**Description:** {$this->group->ai_description}")
            ->action('View Incident Group', url('/admin/incident-groups/'.$this->group->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

<?php

namespace App\Notifications;

use App\Event;
use App\Traits\SmtpSettings;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EventEmail extends Notification implements ShouldQueue
{
    use Queueable, SmtpSettings;

    private $event;
    private $filename;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Event $event, $filename = null)
    {
        $this->event = $event;
        $this->filename = $filename;
        $this->setMailConfigs();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        return (new MailMessage)
            ->subject('Appointment - ' . config('app.name'))
            ->greeting(__('email.hello') . ' ' . ucwords($notifiable->name) . '!')
            ->line(__('email.eventEmailReminder.text'))
            ->line(__('app.name') . ': ' . $this->event->event_name)
            ->line(__('app.time') . ': ' . $this->event->start_date_time->toDayDateTimeString(). ' ~ '. $this->event->end_date_time->toDayDateTimeString())
            ->action(__('email.loginDashboard'), url('/'))
            ->attach(public_path().'/pdf/'.$this->filename, ['mime' => 'application/pdf']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->event->toArray();
    }
}

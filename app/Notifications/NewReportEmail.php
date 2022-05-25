<?php

namespace App\Notifications;

use App\Estimate;
use App\Proposal;
use App\User;
use Illuminate\Bus\Queueable;
use App\Traits\SmtpSettings;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewReportEmail extends Notification implements ShouldQueue
{
    use Queueable, SmtpSettings;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    private $data;
    private $user;
    private $type;
    public function __construct(array $data, User $user, String $type)
    {
        $this->data = $data;
        $this->user = $user;
        $this->type = $type;
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/');

        $message = (new MailMessage)
            ->subject(ucfirst($this->type).' Report'. ' - ' . config('app.name') . '!')
            ->greeting(__('email.hello') . ' '.ucwords($this->user->name).'!')
            ->line('A new '.$this->type.' report has been sent to you. Login now to view the report.');

        foreach ($this->data as $key => $data){
            $message->line($key.': '.$data);
        }
        $message->action('View Report', $url);
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->data;
    }
}

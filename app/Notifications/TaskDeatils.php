<?php

namespace App\Notifications;

use App\EmailNotificationSetting;
use App\SlackSetting;
use App\Task;
use App\Traits\SmtpSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class TaskDeatils extends Notification implements ShouldQueue
{
    use Queueable, SmtpSettings;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $task;
    private $user;
    public function __construct(Task $task, User $user)
    {
        $this->task = $task;
        $this->user = $user;
        $this->emailSetting = email_notification_setting();
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
        $via = ['database', 'mail'];

//        if ($this->emailSetting[7]->send_email == 'yes') {
//            array_push($via, 'mail');
//        }
//
//        if ($this->emailSetting[7]->send_slack == 'yes') {
//            array_push($via, 'slack');
//        }
//
//        if ($this->emailSetting[7]->send_push == 'yes') {
//            array_push($via, OneSignalChannel::class);
//        }

        return $via;
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

        $content = '
            <p><b>Task Name: ' . ucfirst($this->task->heading) . '</b><br/>
            <b>Priority: ' . $this->task->priority . '</b><br/>
            <b>Status: ' . $this->task->status . '</b><br/>
            <b style="color: green">Start On: ' . $this->task->start_date->format('d M, Y') . '</b><br/>
            <b style="color: green">Due On: ' . $this->task->due_date->format('d M, Y') . '</b><br/>
            <b>Assigned By: ' . $this->task->create_by->name . '</b></p>';

        return (new MailMessage)
            ->subject('Task Details - ' . config('app.name') . '!')
            ->greeting(__('email.hello') . ' ' . ucwords($this->user->name) . '!')
            ->markdown('mail.task.task_details', ['url' => $url, 'content' => $content]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->task->id,
            'created_at' => $this->task->created_at->format('Y-m-d H:i:s'),
            'heading' => $this->task->heading
        ];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $slack = SlackSetting::first();
        if (count($notifiable->employee) > 0 && (!is_null($notifiable->employee[0]->slack_username) && ($notifiable->employee[0]->slack_username != ''))) {
            return (new SlackMessage())
                ->from(config('app.name'))
                ->image(asset('storage/slack-logo/' . $slack->slack_logo))
                ->to('@' . $notifiable->employee[0]->slack_username)
                ->content(__('email.newTask.subject'));
        }
        return (new SlackMessage())
            ->from(config('app.name'))
            ->image(asset('storage/slack-logo/' . $slack->slack_logo))
            ->content('This is a redirected notification. Add slack username for *' . ucwords($notifiable->name) . '*');
    }

    public function toOneSignal($notifiable)
    {
        return OneSignalMessage::create()
            ->subject(__('email.newTask.subject'))
            ->body($this->task->heading);
    }
}

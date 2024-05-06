<?php

namespace Noorfarooqy\EasyNotifications\Services;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\EasyNotifications\Services\AfricasTalkingServices;
use Noorfarooqy\EasyNotifications\Services\EmailServices;
use Noorfarooqy\EasyNotifications\Services\SmsServices;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $data;
    protected $notification_type;
    public $sms_channel;
    public function __construct($data, $notification_type='sms', $sms_channel='onfon')
    {
        $this->data = $data;
        $this->notification_type = $notification_type;
        $this->sms_channel = $sms_channel;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if($this->notification_type == 'sms'){
            $this->SendSmsNotification();
        }
        else if($this->notification_type == 'email'){
            $this->SendEmailNotification();
        }
        
    }

    public function SendSmsNotification()
    {
        Log::info('[*] Sending sms notification on channel '.$this->sms_channel);
        if($this->sms_channel == 'onfon' || $this->sms_channel == 'both'){

            $smsServices = new SmsServices();
            $sent_notification = $smsServices->SendSmsUsingOnfon($this->data['to'], $this->data['message']);
        }
        else{
            $smsServices = new AfricasTalkingServices();
            $sent_notification = $smsServices->SendSmsUsingAfricasTalking($this->data['to'], $this->data['message']);
        }
    }
    public function SendEmailNotification()
    {
        $emailServices = new EmailServices();
        $email = [
            'to' => $this->data['to'],
            'email_body' => $this->data['email_body']
        ];
        $email_notification = $emailServices->SendEmail($email, $this->data['email_subject'], $this->data['email_view']);

    }
}

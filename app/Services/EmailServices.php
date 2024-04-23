<?php

namespace Noorfarooqy\EasyNotifications\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Noorfarooqy\EasyNotifications\Mail\EasyNotificationMail;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class EmailServices extends NoorServices
{

    public function SendEmailUsingSmtp($request, $subject='Easy Email', $view_template='en::mail.easy_notification_template')
    {
        $this->request = $request;

        $this->rules = [
            'to' => 'required|email',
            'email_body' => 'required|string|max:10000|min:10',
        ];

        $this->customValidate();
        if($this->has_failed){
            return $this->getResponse();
        }

        $data = $this->validatedData();
        try {
            $email = EasyEmail::create($data);
            Mail::to($data['to'])->send(new EasyNotificationMail($data['email_body'], $subject, $view_template));
            $this->setError('',0);
            $this->setSuccess('success');
            return $this->getResponse($email);
        } catch (\Throwable $th) {
            $this->setError(env('APP_DEBUG')? $th->getMessage() : 'Error sending the email');
            Log::info("[-] Error while sending email ".$th->getMessage());
            return $this->getResponse();
        }

    }

}

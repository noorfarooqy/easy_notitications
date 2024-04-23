<?php

namespace Noorfarooqy\EasyNotifications\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class EmailServices extends NoorServices
{

    public function SendEmailUsingSmtp($request)
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
            Mail::to($data['to'])->send(new EmailTemplate($data['email_body']));
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

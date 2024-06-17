<?php

namespace Noorfarooqy\EasyNotifications\Controllers;
use Illuminate\Http\Request;
use Noorfarooqy\EasyNotifications\Services\AfricasTalkingServices;
use Noorfarooqy\EasyNotifications\Services\EmailServices;
use Noorfarooqy\EasyNotifications\Services\SmsServices;

class EmailController extends Controller
{

    public function sendEmail(Request $request, EmailServices $emailServices)
    {
        return $emailServices->SendEmailUsingSmtp($request);
    }
    public function sendSms(Request $request, AfricasTalkingServices $africasTalkingServices)
    {
        return $africasTalkingServices->SendBulkSmsUsingAfricasTalking($request);
    }

}

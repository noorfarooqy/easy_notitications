<?php

namespace Noorfarooqy\EasyNotifications\Controllers;
use Illuminate\Http\Request;
use Noorfarooqy\EasyNotifications\Services\EmailServices;

class EmailController extends Controller
{

    public function sendEmail(Request $request, EmailServices $emailServices)
    {
        return $emailServices->SendEmailUsingSmtp($request);
    }

}

<?php

namespace Noorfarooqy\EasyNotifications\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class AfricasTalkingServices extends NoorServices
{

    public function getAuthenticationToken()
    {

        $token = EasyNotification::where([
            ['at_token', '!=', null],
            ['has_expired', false]
        ])->get()->first();

        try {
            if (!$token || $token->expires_at < now()) {
                if ($token) {

                    $token->has_expired = true;
                    $token->save();
                }
                $auth_endpoint = config('easy_notifications.africastalking.auth_endpoint');
                $uri = config('easy_notifications.africastalking.api_url') . $auth_endpoint;
                $response = Http::withHeaders([
                    'apiKey' => config('easy_notifications.africastalking.api_key'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Host' => config('easy_notifications.africastalking.api_host'),
                ])->post($uri, [
                            'username' => config('easy_notifications.africastalking.username'),
                        ]);

                if ($response->status() != 200 && $response->status() != 201) {
                    $this->setError($response->body());
                    return false;
                }
                // Log::info($response->body());
                $json_response = $response->json();
                $token = EasyNotification::create([
                    'at_token' => $json_response['token'],
                    'expires_at' => now()->addSeconds($json_response['lifetimeInSeconds'])
                ]);
            }

            return $token;
        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            return false;
        }
    }

    public function SendSmsUsingAfricasTalking($to, $message)
    {
        try {
            $token = $this->getAuthenticationToken();
            if (!$token) {
                return $this->getResponse();
            }
            $url = config('easy_notifications.africastalking.api_url');
            $endpoint = $url . config('easy_notifications.africastalking.sms_endpoint');
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'authToken' => $token->at_token,
                'Host' => config('easy_notifications.africastalking.api_host', 'api.africastalking.com'),
                'Accept-Encoding' => 'gzip, deflate, br',

            ])->asForm()->post($endpoint, [
                        'to' => $to,
                        'message' => $message,
                        'enqueue' => 1,
                        'username' => config('easy_notifications.africastalking.username'),
                        'from' => config('easy_notifications.africastalking.from'),
                    ]);

            $data = [
                'used_token' => $token->id,
                'to' => $to,
                'content' => $message,
                'user' => Auth::user()?->id,
            ];

            if ($response->status() == 200 && $response->status() != 201) {
                $json_response = $response->json();
                $data['is_sent'] = true;
                $data['message_id'] = $json_response['SMSMessageData']['Recipients']['messageId'];
            }
            $data['dlr_response'] = json_encode($response->body());
            $sms = EasySmsNotifications::create($data);
            $this->setError('', 0);
            $this->setSuccess('success');
            return $this->getResponse($sms);
        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            return $this->getResponse();
        }
    }

    public function SendBulkSmsUsingAfricasTalking($request)
    {
        $this->request = $request;
        $this->rules = [
            'to' => 'required|array',
            'to.*' => 'required|numeric',
            'message' => 'required|string',
        ];
        $this->customValidate();
        if ($this->has_failed) {
            return $this->getResponse();
        }
        $count = 0;
        $max_bulk = config('easy_notifications.africastalking.max_bulk_sms', 20);
        $data = $this->validatedData();
        $to = '';
        $message = $data['message'];
        $latest = $this->getResponse();
        foreach ($data['to'] as $number) {
            $to .= $number . ',';
            $count++;
            if ($count == $max_bulk) {
                $latest = $this->SendSmsUsingAfricasTalking($to, $message);
                $to = '';
                $count = 0;
            }
        }

        return $latest;


    }
}

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
                    'Accept' => 'application/json',
                    'apiKey' => config('easy_notifications.africas_talking.api_key'),
                ])->post($uri, [
                    'username' => config('easy_notifications.africastalking.username'),
                ]);

                if ($response->status() != 200 && $response->status() != 201) {
                    $this->setError(json_decode($response->body(), true));
                    return false;
                }
                Log::info($response->body());
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
                'authToken' => $token->at_token
            ])->post($endpoint, [
                'to' => $to,
                'message' => $message,
                'enqueue' => 1,
                'username' => config('easy_notifications.africastalking.username')
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
            $this->setSuccess('', 0);
            return $this->getResponse($sms);
        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            return $this->getResponse();
        }
    }
}

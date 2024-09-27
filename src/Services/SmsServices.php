<?php

namespace Noorfarooqy\EasyNotifications\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\NoorAuth\Services\NoorServices;

class SmsServices extends NoorServices
{

    public $url;

    public function __construct()
    {
        $this->url = config('easy_notifications.onfon.is_sandbox') ?
            config('easy_notifications.onfon.sandbox_url') : config('easy_notifications.onfon.production_url');
    }
    public function AuthorizeOnfon()
    {
        $existing_token = EasyNotification::where('has_expired', false)->get()->first();
        if ($existing_token && now()->lt($existing_token?->expires_at)) {
            return $existing_token;
        } else if ($existing_token && now()->gt($existing_token?->expires_at)) {
            $existing_token->has_expired = true;
            $existing_token->save();
        }

        $endpoint = $this->url . config('easy_notifications.onfon.endpoints.authorization.endpoint');
        $payload = [
            'apiUsername' => config('easy_notifications.onfon.api_username'),
            'apiPassword' => config('easy_notifications.onfon.api_password'),
        ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($endpoint, $payload);
        if (env('APP_DEBUG')) {
            Log::info($endpoint);
            Log::info(json_encode($payload));
            Log::info(json_encode($response->json()));
            Log::info('Response is ok ' . $response->ok());
        }

        if ($response->ok()) {
            try {
                $json_response = $response->json();
                Log::info('parsing respnseonse');
                $token = EasyNotification::create([
                    'onfon_token' => $json_response['token'],
                    'expires_at' => now()->addSeconds((int) $json_response['validDurationSeconds']),
                ]);
                return $token;
            } catch (\Throwable $th) {
                $this->debugLog('Issue parsing onfon response ' . $th->getMessage());
                $this->setError($th->getMessage());
                return false;
            }
        } else {
            $this->setError(json_encode($response->json()));
            return false;
        }
    }
    public function SendSmsUsingOnfon($to, $message, $old_version = false)
    {
        if (config('easy_notifications.onfon.version', 'OLD') == 'OLD') {
            return $this->SendSmsUsingOldVersion($to, $message);
        }

        return $this->SendSmsUsingNewVersion($to, $message);
    }

    public function SendSmsUsingOldVersion($to, $message)
    {

        $endpoint = $this->url . config('easy_notifications.onfon.endpoints.send_sms.endpoint');
        $payload = [

            "SenderId" => config('easy_notifications.onfon.old_version.sender_id'),
            "ApiKey" => config('easy_notifications.onfon.old_version.api_key'),
            "ClientId" => config('easy_notifications.onfon.old_version.client_id'),
            "MessageParameters" => [
                [
                    "Text" => $message,
                    "Number" => $to
                ]
            ]

        ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'AccessKey' => config('easy_notifications.onfon.old_version.access_key'),
        ])->post($endpoint, $payload);
        if (env('APP_DEBUG')) {
            Log::info($endpoint);
            Log::info(json_encode($payload));
        }
        if ($response->ok()) {
            $data = [
                'used_token' => config('easy_notifications.onfon.old_version.access_key'),
                'to' => $to,
                'content' => $message,
                'user' => Auth::user()?->id,
            ];
            try {
                $json_response = $response->json();
                $data['is_sent'] = true;
                $data['message_id'] = $json_response["Data"]['MessageId'];
                $sms = EasySmsNotifications::create($data);
                return $sms;
            } catch (\Throwable $th) {
                $this->setError($th->getMessage());
                $sms = EasySmsNotifications::create($data);
                return false;
            }
        } else {
            $this->setError(json_encode($response->json()));
            return false;
        }
    }
    public function SendSmsUsingNewVersion($to, $message)
    {

        $token = $this->AuthorizeOnfon();
        if (env('APP_DEBUG')) {
            Log::info('Generated token ');
            Log::info(json_encode($token));
        }
        if (!$token) {
            return false;
        }

        $endpoint = $this->url . config('easy_notifications.onfon.endpoints.send_sms.endpoint');
        $payload = [
            "to" => $to,
            "from" => config('easy_notifications.onfon.api_sender_id'),
            "content" => $message,
            "dlr" => config('easy_notifications.onfon.dlr', 'no'),
            "dlr-url" => config('easy_notifications.onfon.dlr_callback'),
            "dlr-level" => 1

        ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token->onfon_token,
        ])->post($endpoint, $payload);
        if (env('APP_DEBUG')) {
            Log::info($endpoint);
            Log::info(json_encode($payload));
        }
        if ($response->ok()) {
            $data = [
                'used_token' => $token->id,
                'to' => $to,
                'content' => $message,
                'user' => Auth::user()?->id,
            ];
            try {
                $json_response = $response->json();
                $data['is_sent'] = true;
                $data['message_id'] = $json_response['message_id'];
                $sms = EasySmsNotifications::create($data);
                return $sms;
            } catch (\Throwable $th) {
                $this->setError($th->getMessage());
                $sms = EasySmsNotifications::create($data);
                return false;
            }
        } else {
            $this->setError(json_encode($response->json()));
            return false;
        }
    }
}

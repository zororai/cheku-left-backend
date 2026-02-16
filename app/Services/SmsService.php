<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.inboxiq.url', 'https://api.inboxiq.co.zw/api/v1');
        $this->username = config('services.inboxiq.username', '');
        $this->password = config('services.inboxiq.password', '');
        $this->apiKey = config('services.inboxiq.api_key', '');
    }

    public function send(string $destination, string $message): bool
    {
        try {
            $authString = base64_encode("{$this->username}:{$this->password}");
            
            // Use URL directly if it ends with /send-sms, otherwise append it
            $url = str_ends_with($this->baseUrl, '/send-sms') 
                ? $this->baseUrl 
                : "{$this->baseUrl}/send-sms";

            $response = Http::withHeaders([
                'Authorization' => "Basic {$authString}",
                'key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'destination' => $destination,
                'messageText' => $message,
            ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'destination' => $destination,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('SMS sending failed', [
                'destination' => $destination,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'destination' => $destination,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendUnlockCodeNotification(string $phone, string $unlockCode, int $additionalPayments): bool
    {
        $message = "Your Cheku Left license has been unlocked! Code: {$unlockCode}. You now have {$additionalPayments} additional payments added to your account.";
        
        return $this->send($phone, $message);
    }
}

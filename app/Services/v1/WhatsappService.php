<?php

namespace App\Services\v1;

use Illuminate\Support\Facades\Http;




class WhatsAppService
{
    protected string $apiUrl;
    protected string $phoneNumberId;
    protected string $accessToken;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.api_url');
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->accessToken = config('whatsapp.access_token');
    }

    public function sendMessage(string $to, string $message): array
    {
        $url = "{$this->apiUrl}{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        $response = Http::withToken($this->accessToken)
            ->post($url, $payload);

        return $response->json();
    }
}

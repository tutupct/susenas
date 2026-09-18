<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GowaService
{
    private function request()
    {
        return Http::withBasicAuth(
            config('services.gowa.username'),
            config('services.gowa.password')
        )->withHeaders([
            'X-Device-Id' => config('services.gowa.device_id'),
        ]);
    }

    public function sendMessage(string $phone, string $message): Response
    {
        return $this->request()
            ->post(
                rtrim(config('services.gowa.url'), '/') . '/send/message',
                [
                    'phone' => $phone,
                    'message' => $message,
                ]
            );
    }

    public function sendImage(string $phone, string $imagePath, ?string $caption = null): Response
    {
        return $this->request()
            ->attach(
                'image',
                fopen($imagePath, 'r'),
                basename($imagePath)
            )
            ->post(
                rtrim(config('services.gowa.url'), '/') . '/send/image',
                [
                    'phone' => $phone,
                    'caption' => $caption,
                ]
            );
    }
}

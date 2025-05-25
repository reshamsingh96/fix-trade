<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class WhatsAppService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected Client $client;

    public function __construct()
    {
        $this->apiKey = env('AISENSY_API_KEY') ?? "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY3MTlmMTRmZjEwOTM4MGJmNTA4YWJhZCIsIm5hbWUiOiJFbGlnbyBDcmVhdGl2ZSBTZXJ2aWNlcyBTaGltbGEiLCJhcHBOYW1lIjoiQWlTZW5zeSIsImNsaWVudElkIjoiNjRmYWZhYzg3MDRlM2I3ZWJlMTRiYTM1IiwiYWN0aXZlUGxhbiI6Ik5PTkUiLCJpYXQiOjE3Mjk3NTM0MjN9.Az3M1Lwa8N8r8v0FtfhsYlP44zTqx4vdbvjjZtjLauQ";
        $this->apiUrl = 'https://backend.aisensy.com/campaign/t1/api/v2';
        $this->client = new Client();
    }

    public function sendMediaMessage(string $userName, string $phone, string $message, string $fileUrl = "", string $fileCaption = "", string $extension = ""): object
    {
        try {
            $cleanMessage = trim(preg_replace('/\s+/', ' ', $message));
            $headers = ['Content-Type' => 'application/json'];

            # Choose campaign name based on media type
            $campaignEnv = match ($extension) {
                'Document' => 'AISENSY_DOCUMENT_CAMPAIGN_NAME',
                'Image' => 'AISENSY_IMAGE_CAMPAIGN_NAME',
                default => 'AISENSY_CAMPAIGN_NAME',
            };

            $payload = [
                'apiKey' => $this->apiKey,
                'campaignName' => env($campaignEnv),
                'destination' => trim($phone),
                'userName' => $userName ?: 'No Name',
                'source' => 'Laravel-System',
                'templateParams' => [$cleanMessage],
                'tags' => ['notification'],
                'attributes' => [
                    'attribute_name' => $cleanMessage,
                ],
            ];

            # Add media if required
            if ($extension === 'Document' || $extension === 'Image') {
                $payload['media'] = [
                    'url' => $fileUrl,
                    'filename' => basename($fileUrl),
                ];
                if (!empty($fileCaption)) {
                    $payload['media']['caption'] = $fileCaption;
                }
            }

            $response = $this->client->post($this->apiUrl, [
                'headers' => $headers,
                'json' => $payload,
            ]);

            return (object)[
                'status' => true,
                'response' => json_decode($response->getBody(), true),
            ];
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $decoded = json_decode($responseBody, true);
            return (object)[
                'status' => false,
                'message' => $decoded['errorMessage'] ?? $decoded['message'] ?? 'Something went wrong',
                'full_error' => $decoded,
            ];
        } catch (\Exception $e) {
            return (object)[
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

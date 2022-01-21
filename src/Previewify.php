<?php

namespace Flowframe\Previewify;

use Flowframe\Previewify\Exception\InvalidRequestException;

class Previewify
{
    public const IMAGE_ENDPOINT = 'https://previewify.app/api/image';

    public const ASYNC_IMAGE_ENDPOINT = 'https://previewify.app/api/async-image';

    public $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @throws InvalidRequestException
     */
    public function image(int $templateId, array $fields): string
    {
        $data = [
            'template_id' => $templateId,
            'fields' => $fields,
        ];

        $signature = hash_hmac('sha256', json_encode($data), $this->apiKey);

        $ch = curl_init(static::IMAGE_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(compact('data', 'signature')));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        $result = json_decode(curl_exec($ch), false);
        $status_code = @curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        if (200 !== $status_code) {
            throw new InvalidRequestException($result->message ?? '', $status_code);
        }

        return $result->url;
    }

    public function asyncImage(int $templateId, array $fields): string
    {
        $data = base64_encode(json_encode([
            'template_id' => $templateId,
            'fields' => $fields,
        ]));

        $signature = hash_hmac('sha256', $data, $this->apiKey);

        $query = http_build_query([
            'data' => $data,
            'signature' => $signature,
        ]);

        return static::ASYNC_IMAGE_ENDPOINT.'?'.$query;
    }
}

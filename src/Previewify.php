<?php

namespace Flowframe\Previewify;

class Previewify
{
    public const IMAGE_ENDPOINT = 'https://previewify.app/api/image';

    public const ASYNC_IMAGE_ENDPOINT = 'https://previewify.app/api/async-image';

    public $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function image(int $templateId, array $fields): string
    {
        $data = [
            'template_id' => $templateId,
            'fields' => $fields,
        ];

        $signature = hash_hmac('sha256', json_encode($data), $this->apiKey);

        $context = stream_context_create(['http' => [
            'header' => "Content-type: application/json\r\nAccept: application/json",
            'method' => 'POST',
            'content' => json_encode(compact('data', 'signature')),
        ]]);

        $result = json_decode(file_get_contents(static::IMAGE_ENDPOINT, false, $context), false);

        return $result->url ?? '';
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

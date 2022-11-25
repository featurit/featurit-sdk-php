<?php

namespace Featurit\Client\HttpClient\Message;

use Psr\Http\Message\ResponseInterface;

class ResponseMediator
{
    public static function getContent(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true)['data'];
    }
}
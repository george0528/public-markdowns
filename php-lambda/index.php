<?php

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require __DIR__ . '/vendor/autoload.php';

Class Handler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // リクエストボディを取得する
        $requestJson = $request->getBody()->getContents();
        $requestBody = json_decode($requestJson, true);
        $name = $requestBody['name'];

        // クエリパラムを取得する
        $params = $request->getQueryParams();
        $name = $params['name'];

        // レスポンス
        $responseBody = [
            'name' => 'george',
            'age' => 20
        ];

        return new Response(200, [], json_encode($responseBody));
    }
}

return new Handler();
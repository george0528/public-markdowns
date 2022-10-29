# はじめに
はじめまして！
ジョージと申します。
先日PHPで書かれた業務委託先のサブシステムを既存サーバーからLambdaに移行する作業を行いました。
その時にPHPでLambdaにデプロイしている記事が少ないように感じたため、今回の記事を書こうと思いました。

# 前提
今回は[bref](https://bref.sh/docs/)というライブラリを使用するため以下の公式ページの手順を事前に行う
- [イントール手順公式ドキュメント](https://bref.sh/docs/installation.html)
  - サーバーレスをインストールする
  - AWSのアクセスキーを作成する
  - Brefをインストールする

# 初期化
次のコマンドを打ってファイルを作成する
```cmd
vendor/bin/bref init
```

以下のように聞かれるため今回は「0」をコマンドで打つ
```cmd
 What kind of lambda do you want to create? (you will be able to add more functions later by editing `serverless.yml`) [Web application]:
  [0] Web application
  [1] Event-driven function
 > 0
```

すると`index.php`と`serverless.yml`ファイルが自動生成される

# ファイルの中身を書き換える
## index.phpの中身を変更する
↓自動生成されたindex.phpファイル
```php:index.php
<?php

// This is a PHP file example.
// Replace it with your application.

// Below is a welcome page written in HTML.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Welcome!</title>
    <link href="https://fonts.googleapis.com/css?family=Dosis:300&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex h-screen">
    <div class="rounded-full mx-auto self-center relative" style="height: 400px; width: 400px; background: linear-gradient(123.19deg, #266488 3.98%, #258ECB 94.36%)">
        <h1 class="font-light absolute w-full text-center text-blue-200" style="font-family: Dosis; font-size: 45px; top: 35%">Hello there,</h1>
        <div class="w-full relative absolute" style="top: 60%; height: 50%">
            <div class="absolute inset-x-0 bg-white" style="bottom: 0; height: 55%"></div>
            <svg viewBox="0 0 1280 311" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0)"><path d="M1214 177L1110.5 215.5L943.295 108.5L807.5 168.5L666 66.5L581 116L517 49.5L288.5 184L163.5 148L-34.5 264.5V311H1317V258.5L1214 177Z" fill="white"/><path d="M1214 177L1110.5 215.5L943.295 108.5L807.5 168.5L666 66.5L581 116L517 49.5L288.5 184L163.5 148L-34.5 264.5L163.5 161L275 194L230.5 281.5L311 189L517 61L628 215.5L600 132.5L666 77L943.295 295L833 184L943.295 116L1172 275L1121 227L1214 189L1298 248L1317 258.5L1214 177Z" fill="#DCEFFA"/></g><defs><clipPath id="clip0"><rect width="1280" height="311" fill="white"/></clipPath></defs></svg>
        </div>
    </div>
</body>
</html>
```

↓書き換える

```php:index.php
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

        return new Response(200, [], $responseBody);
    }
}

return new Handler();
```

## serverless.ymlを書き換える
- サービスの名前を変える(自由)
- リージョンを東京に書き換える(推奨)
- パスとメソッドを変更(自由)
- レイヤーをfpmから変更する(必須)

```yml:serverless.yml
service: php-lambda-test # サービス名を変更

provider:
    name: aws
    region: ap-northeast-1 # リージョンを東京に変更
    runtime: provided.al2

plugins:
    - ./vendor/bref/bref

functions:
    api:
        handler: index.php # ファイル名
        description: ''
        timeout: 28 # in seconds (API Gateway has a timeout of 29 seconds)
        layers:
            - ${bref:layer.php-80} # レイヤーをfpmから変更する
        events:
            -   httpApi: 'GET /' # パスとメソッドを変更

# Exclude files from deployment
package:
    patterns:
        - '!node_modules/**'
        - '!tests/**'

```

# デプロイする
以下のデプロイコマンドを打つ
```cmd
serverless deploy
```

デプロイが成功していれば以下のように表示される
```cmd
$ serverless deploy

Deploying php-lambda-test to stage dev (ap-northeast-1)

✔ Service deployed to stack php-lambda-test-dev (124s)

endpoint: GET - https://oqfwjfhf1j.execute-api.ap-northeast-1.amazonaws.com/
functions:
  api: php-lambda-test-dev-api (956 kB)
```

以下のURLを呼び出す
```cmd
endpoint: GET - https://oqfwjfhf1j.execute-api.ap-northeast-1.amazonaws.com/
```

画像のようにJSONが返ってくる
![APIの結果画像](./%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88%20(619).png)

# 終わりに
最後までご覧いただきありがとうございました！
今回は固定されたJSONを返すだけのAPIをLambdaにデプロイしましたが、今後はLambdaからLambdaを呼び出す処理などもうちょっと複雑な処理について別の記事を書いていこうと思います。
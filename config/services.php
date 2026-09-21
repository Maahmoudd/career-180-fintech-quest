<?php

return ['postmark' => ['token' => env('POSTMARK_TOKEN')], 'resend' => ['key' => env('RESEND_KEY')], 'ses' => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')], 'slack' => ['notifications' => ['bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'), 'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL')]], 'mock_payments' => ['mode' => env('MOCK_PAYMENT_MODE', 'random'), 'path' => env('MOCK_PROVIDER_PATH', database_path('mock-provider.sqlite'))]];

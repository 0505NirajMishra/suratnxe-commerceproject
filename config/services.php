<?php return [
 'postmark'=>['token'=>env('POSTMARK_TOKEN')],
 'resend'=>['key'=>env('RESEND_KEY')],
 'ses'=>['key'=>env('AWS_ACCESS_KEY_ID'),'secret'=>env('AWS_SECRET_ACCESS_KEY'),'region'=>env('AWS_DEFAULT_REGION','us-east-1')],
 'slack'=>['notifications'=>['bot_user_oauth_token'=>env('SLACK_BOT_USER_OAUTH_TOKEN'),'channel'=>env('SLACK_BOT_USER_DEFAULT_CHANNEL')]],
 'razorpay'=>['key'=>env('RAZORPAY_KEY_ID'),'secret'=>env('RAZORPAY_KEY_SECRET'),'webhook_secret'=>env('RAZORPAY_WEBHOOK_SECRET')],
 'stripe'=>['key'=>env('STRIPE_KEY'),'secret'=>env('STRIPE_SECRET')],
 'paypal'=>['client_id'=>env('PAYPAL_CLIENT_ID'),'client_secret'=>env('PAYPAL_CLIENT_SECRET')],
];

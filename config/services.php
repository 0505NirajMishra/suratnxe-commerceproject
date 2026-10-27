<?php return [
 'postmark'=>['token'=>env('POSTMARK_TOKEN')],
 'resend'=>['key'=>env('RESEND_KEY')],
 'ses'=>['key'=>env('AWS_ACCESS_KEY_ID'),'secret'=>env('AWS_SECRET_ACCESS_KEY'),'region'=>env('AWS_DEFAULT_REGION','us-east-1')],
 'slack'=>['notifications'=>['bot_user_oauth_token'=>env('SLACK_BOT_USER_OAUTH_TOKEN'),'channel'=>env('SLACK_BOT_USER_DEFAULT_CHANNEL')]],
 'payu'=>[
  'key'=>env('PAYU_KEY'),
  'salt'=>env('PAYU_SALT'),
  'mode'=>env('PAYU_MODE','test'),
  'payment_url'=>env('PAYU_PAYMENT_URL',env('PAYU_MODE','test') === 'live' ? 'https://secure.payu.in/_payment' : 'https://test.payu.in/_payment'),
  'verify_url'=>env('PAYU_VERIFY_URL',env('PAYU_MODE','test') === 'live' ? 'https://info.payu.in/merchant/postservice.php?form=2' : 'https://test.payu.in/merchant/postservice.php?form=2'),
 ],
 'paypal'=>['client_id'=>env('PAYPAL_CLIENT_ID'),'client_secret'=>env('PAYPAL_CLIENT_SECRET')],
];

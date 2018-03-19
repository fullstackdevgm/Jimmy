<?php

return array(
    'mailchimp' => array(
        'general' => array(
            'apiKey' => '88909aaba99727c416fa51ced5136b1f-us3',
            'listId' => '',
            'apiVersion' => '1.3',
            'timeout' => 300,
            'chunkSize' => 8192,
            'defaultDc' => 'us1',
            'secure' => false,
        ),
        'subscribe' => array(
            'emailType' => 'html',
            'doubleOptin' => false,
            'updateExisting' => false,
            'replaceInterests'=>true,
            'sendWelcome'=>false,
        ),
        'unsubscribe' => array(
            'deleteMember'=>false,
            'sendGoodbye'=>true,
            'sendNotify'=>true,
        ),
    )
);

<?php

namespace MTGofa\FCM\Channels;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Notifications\Notification;

/**
 * Class FirebaseChannel
 * @package MTGofa\FCM\Channels
 */
class FirebaseChannel
{
    /**
     * @const api uri
     */
    const API_URI = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    /**
     * FirebaseChannel constructor.
     * @param Client $client
     * @param Config $config
     */
    public function __construct(Client $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @param $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toFcm($notifiable);
        $message->setTo($notifiable->routeNotificationForFcm());
        
        $apiKey = '';
        if(!empty($message->getApiKey())) {
            //Use the API key provided by the message.
            $apiKey = $message->getApiKey();
        } else {
            //Use the default api key
            $apiKey = $this->getApiKey();
        }

        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_URL, FirebaseChannel::API_URI);
        curl_setopt ($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array (
            'Authorization' => 'key=' . $apiKey,
            'Content-Type' => 'application/json',
        ));
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $message->serialize());
        curl_exec ($ch);
        curl_close ($ch);
    }

    /**
     * @return mixed
     */
    private function getApiKey()
    {
        return $this->config->get('broadcasting.connections.fcm.key');
    }
}
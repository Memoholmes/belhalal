<?php

namespace App\Yantrana\Services\PushBroadcast;

use Exception;
use Pusher\Pusher;

/*
 * PushBroadcast
 *
 *
 *--------------------------------------------------------------------------- */

/**
 * This PushBroadcast class.
 *---------------------------------------------------------------- */
class PushBroadcast
{
    /**
     * $pusher - pusher object
     *-----------------------------------------------------------------------*/
    private $pusher = null;

    /**
     * __construct
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        /**
         * Set up and return PayPal PHP SDK environment with PayPal access credentials.
         * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
         */
        if (getStoreSettings('allow_pusher')) {
            $pusherAppId = getStoreSettings('pusher_app_id');
            $pusherKey = getStoreSettings('pusher_app_key');
            $pusherSecret = getStoreSettings('pusher_app_secret');
            // Pusher call
            $this->pusher = new Pusher(
                $pusherKey,
                $pusherSecret,
                $pusherAppId,
                [
                    'cluster' => getStoreSettings('pusher_app_cluster_key'),
                    'useTLS' => true,
                ]
            );
        }
    }

    /**
     * trigger pusher services
     *-----------------------------------------------------------------------*/
    public function trigger($channels, $event, $data)
    {
        try {
            //trigger channel event to pusher instance
            if (getStoreSettings('allow_pusher')) {
                $this->pusher->trigger($channels, $event, $data);
            }
        } catch (Exception $e) {
            //log error message
            __logDebug($e->getMessage());
        }
    }

    /**
     * account trigger
     *-----------------------------------------------------------------------*/
    public function accountTrigger($event, $data, $commonData = [])
    {
        $channels = [];
        if(empty($commonData) and isset($data['userUid'])) {
            $channels =  [
                'channel-'.$data['userUid']
            ];
            $commonData = $data;
        } else {
            foreach ($data as $dataItem) {
                $channels[] =  'channel-'.$dataItem;
            }
        }
        return $this->trigger($channels, $event, $commonData);
    }

    /**
     * push via notification request
     *-----------------------------------------------------------------------*/
    public function notifyViaPusher($eventId, $data, $commonData = [])
    {
        return $this->accountTrigger($eventId, $data, $commonData);
    }
}

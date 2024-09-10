<?php

/**
 * NotificationEngine.php - Main component file
 *
 * This file is part of the Notification component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Notification;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Notification\Repositories\NotificationRepository;

class NotificationEngine extends BaseEngine
{
    /**
     * @var NotificationRepository - Notification Repository
     */
    protected $notificationRepository;

    /**
     * Constructor.
     *
     * @param  NotificationRepository  $notificationRepository - ManagePages Repository
     *-----------------------------------------------------------------------*/
    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * get notification list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareNotificationList()
    {
        $notificationCollection = $this->notificationRepository->fetchNotificationListData();

        $requireColumns = [
            '_id',
            '_uid',
            'created_at' => function ($pageData) {
                return formatDate($pageData['created_at']);
            },
            'formattedCreatedAt' => function ($pageData) {
                return formatDiffForHumans($pageData['created_at']);
            },
            'is_read',
            'action',
            'formattedIsRead' => function ($key) {
                return (isset($key['is_read']) and $key['is_read'] == 1) ? __tr('Yes') : __tr('No');
            },
            'message',
        ];

        return $this->dataTableResponse($notificationCollection, $requireColumns);
    }

    /**
     * get Api notification list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareApiNotificationList()
    {
        $notificationCollection = $this->notificationRepository->fetchApiNotificationListData();

        $requireColumns = [
            '_id',
            '_uid',
            'created_at' => function ($pageData) {
                return formatDate($pageData['created_at']);
            },
            'formattedCreatedAt' => function ($pageData) {
                return formatDiffForHumans($pageData['created_at']);
            },
            'is_read',
            'action',
            'formattedIsRead' => function ($key) {
                return (isset($key['is_read']) and $key['is_read'] == 1) ? 'Yes' : 'No';
            },
            'message',
        ];

        return $this->customTableResponse($notificationCollection, $requireColumns);
    }

    /**
     * Process Read All Notification.
     *
     *-----------------------------------------------------------------------*/
    public function processReadAllNotification()
    {
        $notification = $this->notificationRepository->fetchAllUnReadNotification();

        //if notification not exists
        if (__isEmpty($notification)) {
            return $this->engineReaction(2, null, __tr('Notification does not exists.'));
        }

        //all notification ids
        //$notificationIds = $notification->pluck('_id')->toArray();
        $notificationData = [];
        if (! __isEmpty($notification)) {
            foreach ($notification as $key => $notify) {
                $notificationData[] = [
                    '_id' => $notify->_id,
                    'is_read' => 1,
                ];
            }
        }

        //update notification
        if ($this->notificationRepository->updateAllNotification($notificationData)) {
            return $this->engineReaction(1, null, __tr('Notification read successfully.'));
        }
        //error response
        return $this->engineReaction(2, null, __tr('Notification not read.'));
    }

    /**
     * Prepare Notification data.
     *
     *-----------------------------------------------------------------------*/
    public function prepareNotificationData()
    {
        return $this->engineReaction(1, getNotificationList());
    }
}

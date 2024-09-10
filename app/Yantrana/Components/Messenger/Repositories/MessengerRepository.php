<?php
/**
* MessengerRepository.php - Repository file
*
* This file is part of the Messenger component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Messenger\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Messenger\Interfaces\MessengerRepositoryInterface;
use App\Yantrana\Components\Messenger\Models\ChatModel;
use Illuminate\Support\Facades\DB;

class MessengerRepository extends BaseRepository implements MessengerRepositoryInterface
{
        /**
     * primary model instance
     * eg. YourModelModel::class;
     *
     * @var object
     */
    protected $primaryModel = ChatModel::class;
    /**
     * Fetch the record of Messenger users
     *
     * @param  number  $userId
     * @return    eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchMessengerUsers($userId)
    {
        return ChatModel::/* where('chats.users__id', $userId)
                        -> */join('users', function ($join) use ($userId) {
            $join->on('chats.from_users__id', '=', 'users._id')
                ->where('chats.to_users__id', $userId)
                ->orOn('chats.to_users__id', '=', 'users._id')
                ->where('chats.from_users__id', $userId);
        })
            ->join('user_authorities', 'users._id', '=', 'user_authorities.users__id')
            ->groupBy('users._id')
            ->leftJoin('user_profiles', 'users._id', '=', 'user_profiles.users__id')
            // Order the users by the latest message
            ->orderBy('chat_created_at', 'desc')
            ->select(
                \__nestedKeyValues([
                    'chats' => [
                        '_id',
                        '_uid',
                        'users__id',
                        'from_users__id',
                        'to_users__id',
                    ],
                    'users' => [
                        '_id AS user_id',
                        '_uid AS user_uid',
                        'first_name',
                        'last_name',
                        'username',
                    ],
                    'user_profiles' => [
                        'users__id AS user_profile_user_id',
                        'profile_picture',
                        'about_me',
                    ],
                    'user_authorities' => [
                        'users__id AS user_authority_user_id',
                        'updated_at',
                    ],
                    // get latest chat
                    DB::raw('MAX(chats.created_at) AS chat_created_at'),
                ])
            )
            ->get();
    }

    /**
     * Fetch the record of Messenger
     *
     * @param  number  $userId
     * @return    eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchConversations($userId, $optionalLoggedInUserId = null)
    {
        return ChatModel::where('chats.users__id', $userId)
            ->where(function ($query) use ($optionalLoggedInUserId) {
                if (__isEmpty($optionalLoggedInUserId)) {
                    $loggedInUserId = getUserID();
                } else {
                    $loggedInUserId = $optionalLoggedInUserId;
                }
                $query->where('chats.to_users__id', $loggedInUserId)
                    ->orWhere('chats.from_users__id', $loggedInUserId);
            })
            ->whereNotIn('chats.type', [9, 10, 11])
            ->join('users AS message_from_user', 'chats.from_users__id', '=', 'message_from_user._id')
            ->join('users AS message_to_user', 'chats.to_users__id', '=', 'message_to_user._id')
            ->select(
                \__nestedKeyValues([
                    'chats' => [
                        '_id',
                        '_uid',
                        'created_at',
                        'status',
                        'message',
                        'type',
                        'from_users__id',
                        'to_users__id',
                        'items__id',
                        'users__id',
                    ],
                    'message_from_user' => [
                        '_id AS message_from_user_id',
                        'first_name AS message_from_first_name',
                        'last_name AS message_from_last_name',
                    ],
                    'message_to_user' => [
                        '_id AS message_to_user_id',
                        'first_name AS message_to_first_name',
                        'last_name AS message_to_last_name',
                    ],
                ])
            )
            ->get();
    }
    /**
     * Fetch the record of Messenger
     *
     * @param  number  $userId
     * @return    eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchFakeUserConversations($userId, $fakeUserId)
    {
        return ChatModel::where('chats.users__id', $userId)
            ->where(function ($query) use ($userId, $fakeUserId) {
                $loggedInUserId = $fakeUserId;
                $query->where('chats.to_users__id', $loggedInUserId)
                    ->orWhere('chats.from_users__id', $loggedInUserId);
            })
            ->whereNotIn('chats.type', [9, 10, 11])
            ->join('users AS message_from_user', 'chats.from_users__id', '=', 'message_from_user._id')
            ->join('users AS message_to_user', 'chats.to_users__id', '=', 'message_to_user._id')
            ->select(
                \__nestedKeyValues([
                    'chats' => [
                        '_id',
                        '_uid',
                        'created_at',
                        'status',
                        'message',
                        'type',
                        'from_users__id',
                        'to_users__id',
                        'items__id',
                        'users__id',
                    ],
                    'message_from_user' => [
                        '_id AS message_from_user_id',
                        'first_name AS message_from_first_name',
                        'last_name AS message_from_last_name',
                    ],
                    'message_to_user' => [
                        '_id AS message_to_user_id',
                        'first_name AS message_to_first_name',
                        'last_name AS message_to_last_name',
                    ],
                ])
            )
            ->get();
    }

    /**
     * Store Message
     *
     * @param  array  $inputData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function storeMessage($inputData)
    {
        $newChatModel = new ChatModel;
        // check if chat messages store
        if ($chatUids = $newChatModel->prepareAndInsert($inputData, '_uid')) {
            return $chatUids;
        }

        return false;
    }

    /**
     * Check if current logged in user can chat with user
     *
     * @param  int  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchMessageRequest($userId, $optionalLoggedInUserId = null)
    {
        if (__isEmpty($optionalLoggedInUserId)) {
            $loggedInUserId = getUserID();
        } else {
            $loggedInUserId = $optionalLoggedInUserId;
        }

        return ChatModel::whereIn('type', [9, 10, 11])
            ->where('users__id', $loggedInUserId)
            ->where(function ($query) use ($userId) {
                $query->where('to_users__id', $userId)
                    ->orWhere('from_users__id', $userId);
            })
            ->first();
    }
    /**
     * Check if current logged in user can chat with user
     *
     * @param  int  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchFakeUserMessageRequest($userId, $fakeUserId)
    {

        return ChatModel::whereIn('type', [9, 10, 11])
            ->where('users__id', $fakeUserId)
            ->where(function ($query) use ($userId) {
                $query->where('to_users__id', $userId)
                    ->orWhere('from_users__id', $userId);
            })
            ->first();
    }

    /**
     * Fetch message receiver data
     *
     * @param  int  $userId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchPendingMessageData($fromUserId, $toUserId)
    {
        return ChatModel::where([
            'from_users__id' => $fromUserId,
            'to_users__id' => $toUserId,
        ])
            ->whereIn('type', [9, 11])
            ->get();
    }

    /**
     * Update message request
     *
     * @param  array  $updateData
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function updateMessages($updateData)
    {
        return ChatModel::bunchUpdate($updateData, '_id');
    }

    /**
     * Fetch by id
     *
     * @param  number  $chatId
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchById($chatId)
    {
        return ChatModel::where('_id', $chatId)->first();
    }

    /**
     * delete Chat
     *
     * @param  obj  $message
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function deleteMessage($message)
    {
        if ($message->delete()) {
            return true;
        }

        return false;
    }

    /**
     * Fetch My
     *
     * @param  obj  $message
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchMyMessages($userId)
    {
        $loggedInUserId = getUserID();

        return ChatModel::where('users__id', $userId)
            ->whereNotIn('type', [9, 10, 11])
            ->get();
    }

    /**
     * Delete all messages
     *
     * @param  obj  $message
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function deleteAllMessages($messageIds)
    {
        if (ChatModel::whereIn('_id', $messageIds)->delete()) {
            return true;
        }

        return false;
    }

    /**
     * Fetch Messenger Data table
     *
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetchMessengerLogData()
    {
        $dataTableConfig = [
            'fieldAlias' => [
                'sender' => 'message_from_user.first_name',
                'receiver' => 'message_to_user.first_name',
                'send_on' => 'chats.created_at',
            ],
            'searchable' => [
            /* 'title',
                'content' */],
        ];

        return ChatModel::whereNotIn('chats.type', [9, 10, 11])
            ->join('users AS message_from_user', 'chats.from_users__id', '=', 'message_from_user._id')
            ->leftJoin('user_profiles AS message_from_user_profile', 'message_from_user._id', '=', 'message_from_user_profile.users__id')
            ->join('users AS message_to_user', 'chats.to_users__id', '=', 'message_to_user._id')
            ->leftJoin('user_profiles AS message_to_user_profile', 'message_to_user._id', '=', 'message_to_user_profile.users__id')
            //->groupBy('chats.from_users__id', 'chats.to_users__id', 'chats.users__id')
            ->groupBy('chats.type', 'chats.from_users__id', 'chats.to_users__id', 'chats.items__id')
            ->select(
                \__nestedKeyValues([
                    'chats' => [
                        '_id',
                        '_uid',
                        'created_at',
                        'status',
                        'message',
                        'type',
                        'from_users__id',
                        'to_users__id',
                        'items__id',
                        'users__id',
                    ],
                    'message_from_user' => [
                        '_id AS message_from_user_id',
                        '_uid AS message_from_user_uid',
                        'first_name AS message_from_first_name',
                        'last_name AS message_from_last_name',
                    ],
                    'message_to_user' => [
                        '_id AS message_to_user_id',
                        '_uid AS message_to_user_uid',
                        'first_name AS message_to_first_name',
                        'last_name AS message_to_last_name',
                    ],
                    'message_from_user_profile' => [
                        'users__id AS message_from_user_profile_user_id',
                        'profile_picture AS message_from_user_profile_picture',
                    ],
                    'message_to_user_profile' => [
                        'users__id AS message_to_user_profile_user_id',
                        'profile_picture AS message_to_user_profile_picture',
                    ],
                ])
            )->dataTables($dataTableConfig)->toArray();
    }


    public function fetchFakeUsers()
    {
        // $query = ChatModel::join('users', 'chats.users__id', '=', 'users._id')
        //     ->where('users.is_fake', 1)
        //     ->orderBy('chat_created_at', 'desc')
        //     ->select(
        //         \__nestedKeyValues([
        //             'users' => [
        //                 '_id AS user_id',
        //                 '_uid AS user_uid',
        //                 'first_name',
        //                 'last_name',
        //                 'username',
        //                 DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) AS full_name"),
        //             ]
        //         ])
        //     )->distinct()->get()->toArray();

        // return $query;



        return ChatModel::/* where('chats.users__id', $userId)
                        -> */join('users', function ($join) {
            $join->on('chats.from_users__id', '=', 'users._id')
                ->where('users.is_fake', 1)
                ->orOn('chats.to_users__id', '=', 'users._id')
                ->where('users.is_fake', 1);
        })

            ->groupBy('users._id')
            ->leftJoin('user_profiles', 'users._id', '=', 'user_profiles.users__id')
            // Order the users by the latest message
            ->orderBy('chat_created_at', 'desc')
            ->select(
                \__nestedKeyValues([
                    'users' => [
                        '_id AS user_id',
                        '_uid AS user_uid',
                        'first_name',
                        'last_name',
                        'username',
                        DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) AS full_name"),
                    ],
                    // get latest chat
                    DB::raw('MAX(chats.created_at) AS chat_created_at'),
                ])
            )
            // ->distinct()
            ->get()->toArray();
    }


    public function fetchUserData($userId)
    {

        return ChatModel::join('users', 'chats.users__id', '=', 'users._id')
            ->where(function ($query) use ($userId) {
                $query->where('to_users__id', $userId)
                    ->orWhere('from_users__id', $userId);
            })
            ->whereNot('users__id', $userId)
            ->select(
                \__nestedKeyValues([
                    'users' => [
                        '_id AS user_id',
                        '_uid AS user_uid',
                        'first_name',
                        'last_name',
                        'username',
                        DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) AS full_name"),
                    ],
                ])
            )
            ->distinct()
            ->get()->toArray();
    }
}

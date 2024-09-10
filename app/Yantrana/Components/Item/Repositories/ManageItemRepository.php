<?php
/**
* ManageItemRepository.php - Repository file
*
* This file is part of the Item component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Item\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Item\Models\ItemModel;
use App\Yantrana\Components\Item\Models\UserItemModel;

class ManageItemRepository extends BaseRepository
{
    /**
     * Constructor.
     *
     * @param  Page  $gift - gift Model
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
    }

    /**
     * fetch all gift list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchListData($type)
    {
        return ItemModel::where('type', $type)->get();
    }

    /**
     * fetch gift data.
     *
     * @param  int  $idOrUid
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetch($idOrUid)
    {
        //check is numeric
        if (is_numeric($idOrUid)) {
            return ItemModel::where('_id', $idOrUid)->first();
        } else {
            return ItemModel::where('_uid', $idOrUid)->first();
        }
    }

    /**
     * store new item.
     *
     * @param  array  $input
     * @return array|object|bool
     *---------------------------------------------------------------- */
    public function storeItem($input)
    {
        $item = new ItemModel;

        $keyValues = [
            'title',
            'type',
            'file_name',
            'normal_price',
            'premium_price',
            'status',
            'user_authorities__id',
            'premium_only',
        ];

        // Store New item
        if ($item->assignInputsAndSave($input, $keyValues)) {
            //check item type is gift or sticker
            if ($item->type == 1) {
                activityLog($item->title.' gift created. ');
            } else {
                activityLog($item->title.' sticker created. ');
            }

            return $item;
        }

        return false;
    }

    /**
     * Update Item Data
     *
     * @param  object  $item
     * @return bool
     *---------------------------------------------------------------- */
    public function updateItem($item, $updateData)
    {
        // Check if information updated
        if ($item->modelUpdate($updateData)) {
            //check item type is gift or sticker
            if ($item->type == 1) {
                activityLog($item->title.' gift updated. ');
            } else {
                activityLog($item->title.' sticker updated. ');
            }

            return true;
        }

        return false;
    }

    /**
     * Delete item.
     *
     * @param  object  $item
     * @return bool
     *---------------------------------------------------------------- */
    public function delete($item)
    {
        // Check if page deleted
        if ($item->delete()) {
            //check item type is gift or sticker
            if ($item->type == 1) {
                activityLog($item->title.' gift deleted. ');
            } else {
                activityLog($item->title.' sticker deleted. ');
            }

            return  true;
        }

        return false;
    }

    /**
     * Fetch by user and item id
     *
     * @param  number  $userId
     * @param  number  $itemId
     * @return bool
     *---------------------------------------------------------------- */
    public function fetchByUserAndItemId($userId, $itemId)
    {
        return UserItemModel::where([
            'users__id' => $userId,
            'items__id' => $itemId,
        ])->first();
    }

    /**
     * Fetch All Active Stickers
     *
     * @return eloquent collection
     *---------------------------------------------------------------- */
    public function fetchStickers($isPremiumUser)
    {
        $userId = getUserID();

        return ItemModel::where([
            'items.type' => 2,
            'items.status' => 1,
        ])
            ->leftJoin('user_items', function ($join) use ($userId) {
                $join->on('items._id', '=', 'user_items.items__id')
                    ->where('user_items.users__id', $userId);
            })
            ->where(function ($query) use ($isPremiumUser) {
                if (! $isPremiumUser) {
                    $query->where('items.premium_only', null);
                }
            })
            ->select(
                \__nestedKeyValues([
                    'items' => ['*'],
                    'user_items' => [
                        'users__id',
                        'items__id',
                        'price',
                    ],
                ])
            )
            ->get();
    }

    public function storeUserItem($userItemStoreData)
    {
        $keyValues = [
            'status' => 1,
            'users__id',
            'items__id',
            'price',
            'credit_wallet_transactions__id',
        ];

        $userItemModel = new UserItemModel;

        // Check if user item added
        if ($userItemModel->assignInputsAndSave($userItemStoreData, $keyValues)) {
            return true;
        }

        return false;   // on failed
    }
}

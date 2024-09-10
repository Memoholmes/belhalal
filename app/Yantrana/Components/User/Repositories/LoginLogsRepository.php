<?php

/**
 * LoginLogsRepository.php - Repository file
 *
 * This file is part of the Credit Wallet User component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\User\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\User\Models\loginLogsModel;
use Request;

class LoginLogsRepository extends BaseRepository
{
    /**
     * primary model instance
     * eg. YourModelModel::class;
     *
     * @var object
     */
    protected $primaryModel = loginLogsModel::class;



    /**
     * Update User Login Logs
     *
     * @param   object  $userLogs
     * @param   array  $updateData
     *
     * @return  boolean
     */
    static function updateLoginLogs($userLogs, $updateData)
    {
        // Check if information updated
        if ($userLogs->modelUpdate($updateData)) {
            return true;
        }

        return false;
    }


    public function createLoginLog($userDetails)
    {

        $keyValues = ['email', 'ip_address', 'updated_at', 'role', 'user_id'];
        $loginLogs = loginLogsModel::where('user_id', $userDetails->_id)->first();

        $storeData = [
            'email' => $userDetails->email,
            'ip_address' => Request::getClientIp(),
            'updated_at' => getCurrentDateTime(),
            'user_id' => $userDetails->_id
        ];

        if (!empty($loginLogs)) {
            $this->updateLoginLogs($loginLogs, ['updated_at' => getCurrentDateTime()]);
        } else {
            $loginLogModel = new loginLogsModel;

            // Store New User
            if ($loginLogModel->assignInputsAndSave($storeData, $keyValues)) {
                return true;
            } else {
                return false;
            }
        }
    }
}

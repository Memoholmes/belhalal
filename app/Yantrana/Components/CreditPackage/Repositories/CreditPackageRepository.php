<?php
/**
* CreditPackageRepository.php - Repository file
*
* This file is part of the CreditPackage component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\CreditPackage\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\CreditPackage\Interfaces\CreditPackageRepositoryInterface;
use App\Yantrana\Components\CreditPackage\Models\CreditPackageModel;

class CreditPackageRepository extends BaseRepository implements CreditPackageRepositoryInterface
{
        /**
     * primary model instance
     * eg. YourModelModel::class;
     *
     * @var object
     */
    protected $primaryModel = CreditPackageModel::class;

    /**
     * Fetch the record of CreditPackage
     *
     * @param    int || string $idOrUid
     * @return    eloquent collection object
     *---------------------------------------------------------------- */
    public function fetch($idOrUid)
    {
        if (is_numeric($idOrUid)) {
            return CreditPackageModel::where('_id', $idOrUid)->first();
        }

        return CreditPackageModel::where('_uid', $idOrUid)->first();
    }

    /**
     * fetch all credit package list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchAllCreditPackage()
    {
        return CreditPackageModel::get();
    }

    /**
     * fetch all active credit package list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchAllActiveCreditPackage()
    {
        return CreditPackageModel::where('status', 1)
            ->get();
    }

    /**
     * store new package.
     *
     * @param  array  $input
     * @return mixed
     *---------------------------------------------------------------- */
    public function storePackage($input)
    {
        $keyValues = [
            'title',
            'price',
            'credits',
            'image',
            'status',
            'users__id',
        ];
        $creditPackage = new CreditPackageModel;
        // Store New package
        if ($creditPackage->assignInputsAndSave($input, $keyValues)) {
            //package add activity log
            activityLog($creditPackage->title.' package created. ');

            return $creditPackage;
        }

        return false;
    }

    /**
     * Update Package Data
     *
     * @param  object  $packageData
     * @return bool
     *---------------------------------------------------------------- */
    public function updatePackage($packageData, $updateData)
    {
        // Check if information updated
        if ($packageData->modelUpdate($updateData)) {
            //package update activity log
            activityLog($packageData->title.' package updated. ');

            return true;
        }

        return false;
    }

    /**
     * Delete package.
     *
     * @param  object  $packageData
     * @return bool
     *---------------------------------------------------------------- */
    public function delete($packageData)
    {
        // Check if package deleted
        if ($packageData->delete()) {
            //package delete activity log
            activityLog($packageData->title.' package deleted.');

            return  true;
        }

        return false;
    }
}

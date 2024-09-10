<?php
/**
* ManagePagesRepository.php - Repository file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Pages\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Pages\Blueprints\ManagePagesRepositoryBlueprint;
use App\Yantrana\Components\Pages\Models\PageModel;

class ManagePagesRepository extends BaseRepository implements ManagePagesRepositoryBlueprint
{
    /**
     * Constructor.
     *
     * @param  Page  $page - page Model
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
    }

    /**
     * fetch all pages list.
     *
     * @return array
     *---------------------------------------------------------------- */
    public function fetchListData()
    {
        $dataTableConfig = [
            'searchable' => [
                'title',
                'content',
            ],
        ];

        return PageModel::dataTables($dataTableConfig)->toArray();
    }

    /**
     * fetch page data.
     *
     * @param  int  $idOrUid
     * @return eloquent collection object
     *---------------------------------------------------------------- */
    public function fetch($idOrUid)
    {
        //check is numeric
        if (is_numeric($idOrUid)) {
            return PageModel::where('_id', $idOrUid)->first();
        } else {
            return PageModel::where('_uid', $idOrUid)->first();
        }
    }

    /**
     * store new page.
     *
     * @param  array  $input
     * @return array
     *---------------------------------------------------------------- */
    public function store($input)
    {
        $page = new PageModel;

        $keyValues = [
            'title',
            'type',
            'content',
            'status',
            'users__id',
        ];

        // Store New Page
        if ($page->assignInputsAndSave($input, $keyValues)) {
            activityLog($page->title.' page created. ');

            return true;
        }

        return false;
    }

    /**
     * Update Page Data
     *
     * @param  object  $page
     * @return bool
     *---------------------------------------------------------------- */
    public function update($page, $updateData)
    {
        // Check if information updated
        if ($page->modelUpdate($updateData)) {
            activityLog($page->title.' page updated. ');

            return true;
        }

        return false;
    }

    /**
     * Delete page.
     *
     * @param  object  $page
     * @return bool
     *---------------------------------------------------------------- */
    public function delete($page)
    {
        // Check if page deleted
        if ($page->delete()) {
            activityLog($page->title.' page deleted. ');

            return  true;
        }

        return false;
    }
}

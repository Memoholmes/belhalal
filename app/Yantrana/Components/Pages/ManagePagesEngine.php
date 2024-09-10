<?php
/**
* ManagePagesEngine.php - Main component file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Pages;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Pages\Repositories\ManagePagesRepository;
use Auth;

class ManagePagesEngine extends BaseEngine
{
    /**
     * @var ManagePagesRepository - ManagePages Repository
     */
    protected $managePagesRepository;

    /**
     * Constructor.
     *
     * @param  ManagePagesRepository  $managePagesRepository - ManagePages Repository
     *-----------------------------------------------------------------------*/
    public function __construct(ManagePagesRepository $managePagesRepository)
    {
        $this->managePagesRepository = $managePagesRepository;
    }

    /**
     * get page list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function preparePageList()
    {
        $pageCollection = $this->managePagesRepository->fetchListData();

        $requireColumns = [
            '_id',
            '_uid',
            'title',
            'created_at' => function ($pageData) {
                return formatDate($pageData['created_at']);
            },
            'updated_at' => function ($pageData) {
                return formatDate($pageData['updated_at']);
            },
            'status' => function ($pageData) {
                return configItem('status_codes', $pageData['status']);
            },
            'preview_url' => function ($page) {
                return route('page.preview', [
                    'pageUId' => $page['_id'],
                    'title' => slugIt($page['title']),
                ]);
            },
        ];

        return $this->dataTableResponse($pageCollection, $requireColumns);
    }

    /**
     * Process add new page.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function prepareForAddNewPage($inputData)
    {
        $storeData = [
            'title' => $inputData['title'],
            'type' => 1,
            'content' => $inputData['description'],
            'status' => (isset($inputData['status']) and $inputData['status'] == 'on') ? 1 : 2,
            'users__id' => Auth::id(),
        ];

        //Check if page added
        if ($this->managePagesRepository->store($storeData)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Page added successfully'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Page not added.'));
    }

    /**
     * get page edit data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareUpdateData($pageUId)
    {
        $pageCollection = $this->managePagesRepository->fetch($pageUId);

        //if is empty
        if (__isEmpty($pageCollection)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Page does not exist'));
        }

        $pageEditData = [];
        if (! __isEmpty($pageCollection)) {
            $pageEditData = [
                '_id' => $pageCollection['_id'],
                '_uid' => $pageCollection['_uid'],
                'title' => $pageCollection['title'],
                'description' => $pageCollection['content'],
                'created_at' => formatDate($pageCollection['created_at']),
                'updated_at' => formatDate($pageCollection['updated_at']),
                'status' => $pageCollection['status'],
                'preview_url' => route('page.preview', [
                    'pageUId' => $pageCollection['_uid'],
                    'title' => slugIt($pageCollection['title']),
                ]),
            ];
        }

        return $this->engineReaction(1, [
            'pageEditData' => $pageEditData,
        ]);
    }

    /**
     * Process add new page.
     *
     * @param  array  $inputData
     *---------------------------------------------------------------- */
    public function prepareForEditNewPage($inputData, $pageUId)
    {
        $pageCollection = $this->managePagesRepository->fetch($pageUId);

        //if is empty
        if (__isEmpty($pageCollection)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Page does not exist'));
        }

        //update data
        $updateData = [
            'title' => $inputData['title'],
            'content' => $inputData['description'],
            'status' => (isset($inputData['status']) and $inputData['status'] == 'on') ? 1 : 2,
        ];

        //Check if page updated
        if ($this->managePagesRepository->update($pageCollection, $updateData)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Page updated successfully'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Page not updated.'));
    }

    /**
     * Process delete.
     *
     * @param int pageUId
     * @return array
     *---------------------------------------------------------------- */
    public function processDelete($pageUId)
    {
        $pageCollection = $this->managePagesRepository->fetch($pageUId);

        //if is empty
        if (__isEmpty($pageCollection)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Page does not exist.'));
        }

        //Check if page deleted
        if ($this->managePagesRepository->delete($pageCollection)) {
            return $this->engineReaction(1, [
                'pageUId' => $pageCollection->_uid,
            ], __tr('Page deleted successfully.'));
        }

        return $this->engineReaction(18, ['show_message' => true], __tr('Page not deleted.'));
    }

    /**
     * Preview  page data
     *
     * @param string pageUId
     * @return array
     *---------------------------------------------------------------- */
    public function previewPage($pageUId)
    {
        $page = $this->managePagesRepository->fetch($pageUId);

        //if is empty or if page status is in active then abort this page request
        if (__isEmpty($page) or $page['status'] == 2) {
            abort(404);
        }

        return $this->engineReaction(1, [
            'page' => [
                'title' => $page->title,
                'content' => $page->content,
            ],
        ]);
    }
}

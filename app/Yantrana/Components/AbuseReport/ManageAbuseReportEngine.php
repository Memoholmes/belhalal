<?php
/**
* ManageAbuseReportEngine.php - Main component file
*
* This file is part of the Pages component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\AbuseReport;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\AbuseReport\Repositories\ManageAbuseReportRepository;
use App\Yantrana\Components\User\Repositories\UserRepository;

class ManageAbuseReportEngine extends BaseEngine
{
    /**
     * @var ManageAbuseReportRepository - ManageAbuseReport Repository
     */
    protected $manageAbuseReportRepository;

    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * Constructor.
     *
     * @param  ManageAbuseReportRepository  $ManageAbuseReportRepository - ManageAbuseReport Repository
     *-----------------------------------------------------------------------*/
    public function __construct(ManageAbuseReportRepository $manageAbuseReportRepository, UserRepository $userRepository)
    {
        $this->manageAbuseReportRepository = $manageAbuseReportRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * get report list data.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function prepareReportList($status)
    {
        //get report list collection
        $reportCollection = $this->manageAbuseReportRepository->fetchListData($status);

        $reportListData = [];
        if (! __isEmpty($reportCollection)) {
            //pluck for user ids
            $forUserIds = $reportCollection->pluck('for_users__id')->toArray();

            //fetch report by users
            $reportedByUser = $this->manageAbuseReportRepository->fetchReportByUser($forUserIds);
            $reportedUserCollection = $reportedByUser->collect()->groupBy('for_users__id');

            $reportedUserData = [];
            $count = [];
            //collect report data in array
            foreach ($reportedUserCollection as $reportUserKey => $reportByUser) {
                $count[$reportUserKey] = $reportByUser->count();
                //check is not empty array
                if (! __isEmpty($reportByUser)) {
                    foreach ($reportByUser as $key => $user) {
                        $reportedUserData[$reportUserKey][] = [
                            '_id' => $user['_id'],
                            'created_at' => formatDate($user['created_at']),
                            'updated_at' => formatDate($user['updated_at']),
                            'status' => $user['status'],
                            'for_users__id' => $user['for_users__id'],
                            'by_users__id' => $user['by_users__id'],
                            'reason' => $user['reason'],
                            'userId' => $user['userId'],
                            'reportedByUser' => $user['reportedByUser'],
                            // 'reported_by_user_profile_link' 	=> $user['reported_by_user_username'],
                            'reported_by_user_profile_url' => route('user.profile_view', ['username' => $user['reported_by_user_username']]),
                        ];
                    }
                }
            }

            //collect report data in array
            foreach ($reportCollection as $key => $report) {
                $reportListData[] = [
                    '_id' => $report['_id'],
                    '_uid' => $report['_uid'],
                    'reported_user' => $report['reportedUserName'],
                    'total_report_count' => $count[$report->for_users__id],
                    'reportedByUser' => $reportedUserData[$report->for_users__id],
                    'for_users__id' => $report->for_users__id,
                    'created_at' => formatDate($report['created_at']),
                    'updated_at' => formatDate($report['updated_at']),
                    'status' => $report['status'],
                    'formattedStatus' => configItem('report_user_status_codes', $report['status']),
                    'moderator_remarks' => $user['moderator_remarks'],
                    'reported_user_username' => $report['reported_user_username'],
                ];
            }
        }

        return $this->engineReaction(1, [
            'reportListData' => $reportListData,
        ]);
    }

    /**
     * process moderate user report.
     *
     *
     * @return object
     *---------------------------------------------------------------- */
    public function processModerateReport($inputData)
    {
        $reportCollection = $this->manageAbuseReportRepository->fetchReportByUser([$inputData['forUserId']]);

        //if is empty then show error message
        if (__isEmpty($reportCollection)) {
            return $this->engineReaction(1, ['show_message' => true], __tr('Report does not exist'));
        }

        $updateData = [];
        // check if not empty
        if (! __isEmpty($reportCollection)) {
            foreach ($reportCollection as $key => $reportData) {
                //update data
                $updateData[] = [
                    '_id' => $reportData['_id'],
                    'for_users__id' => $reportData['for_users__id'],
                    'moderator_remarks' => $inputData['moderator_remarks'],
                    'status' => $inputData['reportStatus'],
                    'moderated_by_users__id' => getUserID(),
                ];
            }
        }

        //Check if report updated
        if ($this->manageAbuseReportRepository->batchUpdate($updateData)) {
            //check user block report is accepted
            if ($inputData['reportStatus'] == 2 || $inputData['reportStatus'] == 3) {
                //fetch user
                $user = $this->userRepository->fetch($inputData['forUserId']);

                if ($user->user_role_id == 1) { // if admin user
                    return $this->engineReaction(2, ['show_message' => true], __tr('Admin cannot moderated'));
                }

                //collect update data
                $updateData = [
                    'status' => (isset($inputData['reportStatus']) and $inputData['reportStatus'] == 2) ? 3 : 1, // block
                    'block_reason' => 'Abuse report by admin',
                ];

                // Check if user activated successfully
                if ($this->userRepository->updateUser($user, $updateData)) {
                    return $this->engineReaction(1, ['show_message' => true], __tr('Report moderated successfully'));
                }
            } else {
                return $this->engineReaction(1, ['show_message' => true], __tr('Report moderated successfully'));
            }
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Report not moderated.'));
    }

    public function abuseReportsCount()
    {
        $reportsCount = $this->manageAbuseReportRepository->fetchItAll([
            'status' => 1
        ])->count();

        return $this->engineReaction(1, ['reportsCount' => $reportsCount]);
    }
}

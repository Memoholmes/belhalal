<?php

namespace App\Yantrana\Base;

use App\Yantrana\__Laraware\Core\CoreMailer;
use App\Yantrana\Components\User\Repositories\UserRepository;
use Exception;
use Log;

/*
 * Base Mailer
 *--------------------------------------------------------------------------- */

class BaseMailer extends CoreMailer
{
    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;

    /**
     * Constructor.
     *
     * @param  UserRepository  $userRepository - User Repository
     *-----------------------------------------------------------------------*/
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Notify Administrator.
     *
     * @param  sting  $subject.
     * @param  sting  $emailView.
     * @param  array  $messageData.
     * @return array.
     */
    public function notifyAdmin($subject, $emailView, $messageData = [], $messageType = 1)
    {
        
        $messageData['emailsTemplate'] = 'emails.'.$emailView;
        //$messageData['name'] = getStoreSettings('store_name').' Administrator';
        $messageData['mailForAdmin'] = true;
        $messageData['mailForCustomer'] = false;

        $adminEmails = [
            1 => getStoreSettings('business_email'),
            2 => getStoreSettings('contact_email'),
        ];

        if (__isEmpty($adminEmails[$messageType])) {
            return false;
        }
       
        try {
            return $this->send([
                'recipients' => $adminEmails[$messageType],
                'subject' => $subject, //$subject,
                'view' => 'emails.index',
                'from' => (getStoreSettings('use_env_default_email_settings') == 0) ? getStoreSettings('mail_from_address') : configItem('mail_from'),
                'messageData' => $messageData,
                'replyTo' => __ifIsset($messageData['senderEmail'])
                    ? [$messageData['senderEmail'], $messageData['userName']]
                    : [],
            ]);
        } catch (Exception $e) {
            if (config('app.debug', false)) {
                Log::debug($e->getMessage());
            }

            return false;
        }
    }

    /**
     * Notify Customer.
     *
     * @param  sting  $subject.
     * @param  sting  $emailView.
     * @param  array  $messageData.
     * @param  mixed  $customerEmailOrId.
     * @return array.
     */
    public function notifyToUser($subject, $emailView, $messageData = [], $customerEmailOrId = null)
    {
        $customerName = isset($messageData['name']) ? $messageData['name'] : null;

        if (isLoggedIn()) {
            $userAuthInfo = getUserAuthInfo();
            $customerEmail = $userAuthInfo['profile']['email'];
            $customerName = $userAuthInfo['profile']['full_name'];
        }
        // if customer email or id sent
        if ($customerEmailOrId) {
            // set it as customer email address
            $customerEmail = $customerEmailOrId;

            // if its a user id then find user & get email address of it
            if (is_numeric($customerEmailOrId)) {
                $userInfo = $this->userRepository->fetchByID($customerEmailOrId);

                $customerEmail = $userInfo->email;
                $customerName = $userInfo->first_name.' '.$userInfo->last_name;
            }
        }

        if (! $customerEmail) {
            throw new Exception('Customer Email is required');
        }

        if (! $customerName) {
            $customerName = $customerEmail;
        }

        $messageData['emailsTemplate'] = 'emails.'.$emailView;
        $messageData['mailForAdmin'] = false;
        $messageData['mailForCustomer'] = true;

        try {
            return $this->send([
                'recipients' => $customerEmail,
                'replyTo' => (getStoreSettings('use_env_default_email_settings') == 0) ? getStoreSettings('mail_from_address') : configItem('mail_from'),
                'subject' => $subject,
                'view' => 'emails.index',
                'from' => (getStoreSettings('use_env_default_email_settings') == 0) ? getStoreSettings('mail_from_address') : configItem('mail_from'),
                'messageData' => $messageData,
            ]);
        } catch (Exception $e) {
            if (config('app.debug', false)) {
                Log::debug($e->getMessage());
            }

            return false;
        }
    }
}

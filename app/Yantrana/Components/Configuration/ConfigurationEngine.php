<?php

/**
 * ConfigurationEngine.php - Main component file
 *
 * This file is part of the Configuration component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Configuration;

use YesSecurity;
use Illuminate\Support\Arr;
use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Support\CommonTrait;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\Configuration\Repositories\ConfigurationRepository;
use App\Yantrana\Components\CreditPackage\Repositories\CreditPackageRepository;
use App\Yantrana\Components\Configuration\Interfaces\ConfigurationEngineInterface;

class ConfigurationEngine extends BaseEngine implements ConfigurationEngineInterface
{
    /**
     * @var CommonTrait - Common Trait
     */
    use CommonTrait;

    /**
     * @var  ConfigurationRepository - Configuration Repository
     */
    protected $configurationRepository;

    /**
     * @var  MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * @var  CreditPackageRepository - CreditPackage Repository
     */
    protected $creditPackageRepository;

    /**
     * Constructor
     *
     * @param  ConfigurationRepository  $configurationRepository - Configuration Repository
     * @param  MediaEngine  $mediaEngine - Media Engine
     * @param  CreditPackageRepository  $creditPackageRepository - CreditPackage Repository
     * 
     * @return  void
     *-----------------------------------------------------------------------*/
    public function __construct(
        ConfigurationRepository $configurationRepository,
        MediaEngine $mediaEngine,
        CreditPackageRepository $creditPackageRepository
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->mediaEngine = $mediaEngine;
        $this->creditPackageRepository = $creditPackageRepository;
    }

    /**
     * Prepare Configuration.
     *
     * @param  string  $pageType
     * @return array
     *---------------------------------------------------------------- */
    public function prepareConfigurations($pageType)
    {
        // Get settings from config
        $defaultSettings = $this->getDefaultSettings(config('__settings.items.' . $pageType));

        // check if default settings exists
        if (__isEmpty($defaultSettings)) {
            return $this->engineReaction(18, null, __tr('Invalid page type.'));
        }
        $configurationSettings = $dbConfigurationSettings = [];
        // Check if default settings exists
        if (!__isEmpty($defaultSettings)) {
            // Get selected default settings
            $configurationCollection = $this->configurationRepository->fetchByNames(array_keys($defaultSettings));
            // check if configuration collection exists
            if (!__isEmpty($configurationCollection)) {
                foreach ($configurationCollection as $configuration) {
                    $dbConfigurationSettings[$configuration->name] = $this->castValue($configuration->data_type, $configuration->value);
                }
            }
            // Loop over the default settings
            foreach ($defaultSettings as $defaultSetting) {
                $configurationSettings[$defaultSetting['key']] = $this->prepareDataForConfiguration($dbConfigurationSettings, $defaultSetting);
            }
        }
        //check page type is currency
        if ($pageType == 'general') {
            $configurationSettings['timezone_list'] = $this->getTimeZone();
            $languages = getStoreSettings('translation_languages');
            //set default language
            $languageList[] = [
                'id' => 'en_US',
                'name' => 'Default Language (English)',
                'status' => true,
            ];

            //check is not empty
            if (!__isEmpty($languages)) {
                foreach ($languages as $key => $language) {
                    if ($language['status']) {
                        $languageList[] = [
                            'id' => $language['id'],
                            'name' => $language['name'],
                            'status' => $language['status'],
                        ];
                    }
                }
            }
            $configurationSettings['languageList'] = $languageList;
        } elseif ($pageType == 'currency') {
            $configurationSettings['currencies'] = config('__currencies.currencies');
            $configurationSettings['currency_options'] = $this->generateCurrenciesArray($configurationSettings['currencies']['details']);
        } elseif ($pageType == 'premium-plans') {
            $defaultPlanDuration = $defaultSettings['plan_duration']['default'];
            $dbPlanDuration = $configurationSettings['plan_duration'];
            $configurationSettings['plan_duration'] = combineArray($defaultPlanDuration, $dbPlanDuration);
        } elseif ($pageType == 'premium-feature') {
            $defaultFeaturePlans = $defaultSettings['feature_plans']['default'];
            $dbFeaturePlans = $configurationSettings['feature_plans'];
            $configurationSettings['feature_plans'] = combineArray($defaultFeaturePlans, $dbFeaturePlans);
        } elseif ($pageType == 'email') {
            $configurationSettings['mail_drivers'] = configItem('mail_drivers');
            $configurationSettings['sms_drivers'] = configItem('sms_drivers');
            $configurationSettings['mail_encryption_types'] = configItem('mail_encryption_types');
        } elseif ($pageType == 'user') {
            $configurationSettings['admin_choice_display_mobile_number'] = configItem('admin_choice_display_mobile_number');
        }
        if ($pageType == 'custom-profile-fields') {
            $configurationSettings = getStoreSettings('custom_profiles');
        }
        return $this->engineReaction(1, [
            'configurationData' => $configurationSettings,
        ]);
    }

    /**
     * Generate currency array.
     *
     * @param  string  $pageType
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    protected function generateCurrenciesArray($currencies)
    {
        $currenciesArray = [];
        foreach ($currencies as $key => $currency) {
            $currenciesArray[] = [
                'currency_code' => $key,
                'currency_name' => $currency['name'],
            ];
        }

        $currenciesArray[] = [
            'currency_code' => 'other',
            'currency_name' => 'other',
        ];

        return $currenciesArray;
    }

    /**
     * Process Configuration Store.
     *
     * @param  string  $pageType
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processConfigurationsStore($pageType, $inputData)
    {
        
        $dataForStoreOrUpdate = $configurationKeysForDelete = [];
        $isDataAddedOrUpdated = false;

        // Get settings from config
        $defaultSettings = $this->getDefaultSettings(config('__settings.items.' . $pageType));
        

        // check if default settings exists
        if (__isEmpty($defaultSettings)) {
            return $this->engineReaction(18, ['show_message' => true], __tr('Invalid page type.'));
        }

        //manage create premium plan duration array
        if ($pageType == 'premium-plans') {
            if (!__isEmpty($inputData['plan_duration'])) {
                foreach ($inputData['plan_duration'] as $key => $plan) {
                    $inputData['plan_duration'][$key] = [
                        'enable' => (isset($plan['enable']) and $plan['enable'] == 'true') ? true : false,
                        'price' => $plan['price'],
                    ];
                }
            }
        }

        $isExtendedLicence = (getStoreSettings('product_registration', 'licence') === 'dee257a8c3a2656b7d7fbe9a91dd8c7c41d90dc9');

        // Check if input data exists
        if (!__isEmpty($inputData)) {
            // Get selected default settings
            $configurationCollection = $this->configurationRepository->fetchByNames(array_keys($defaultSettings))->pluck('value', 'name')->toArray();
            foreach ($inputData as $inputKey => $inputValue) {
                // Check if default text and form text not same
                if (array_key_exists($inputKey, $defaultSettings) and ($inputValue != $defaultSettings[$inputKey]['default'] or !__isEmpty($defaultSettings[$inputKey]['default']))) {
                    $castValues = $this->castValue(
                        ($defaultSettings[$inputKey]['data_type'] == 4)
                            ? 5 : $defaultSettings[$inputKey]['data_type'], // for Encode purpose only
                        $inputValue
                    );
                    if (array_get($defaultSettings[$inputKey], 'hide_value') and $defaultSettings[$inputKey]['hide_value'] and !__isEmpty($inputValue)) {
                        $dataForStoreOrUpdate[] = [
                            'name' => $inputKey,
                            'value' => $castValues,
                            'data_type' => $defaultSettings[$inputKey]['data_type'],
                        ];
                    } elseif (!array_get($defaultSettings[$inputKey], 'hide_value')) {
                        $dataForStoreOrUpdate[] = [
                            'name' => $inputKey,
                            'value' => $castValues,
                            'data_type' => $defaultSettings[$inputKey]['data_type'],
                        ];
                    }
                }
                if (!$isExtendedLicence and in_array($inputKey, [
                    'use_test_razorpay',
                    'use_test_stripe',
                    'use_test_paypal_checkout',
                ]) and !$inputValue) {
                    return $this->engineReaction(2, [
                        'show_message' => true,
                    ], __tr('You need to purchase extended license to use live keys.'));
                }
                // Check if default value and input value same and it is exists
                if ((array_key_exists($inputKey, $defaultSettings))
                    and ($inputValue == $defaultSettings[$inputKey]['default'])
                    and (!isset($defaultSettings[$inputKey]['hide_value']))
                ) {
                    if (array_key_exists($inputKey, $configurationCollection)) {
                        $configurationKeysForDelete[] = $inputKey;
                    }
                }

                continue;
            }
            // Send data for store or update
            if (
                !__isEmpty($dataForStoreOrUpdate)
                and $this->configurationRepository->storeOrUpdate($dataForStoreOrUpdate)
            ) {
                activityLog('Site configuration settings stored / updated.');
                $isDataAddedOrUpdated = true;
            }

            // Check if deleted keys deleted successfully
            if (
                !__isEmpty($configurationKeysForDelete)
                and $this->configurationRepository->deleteConfiguration($configurationKeysForDelete)
            ) {
                $isDataAddedOrUpdated = true;
            }

            // Check if data added / updated or deleted
            if ($isDataAddedOrUpdated) {
                return $this->engineReaction(1, ['show_message' => true], __tr('Configuration updated successfully.'));
            }

            return $this->engineReaction(14, ['show_message' => true], __tr('Nothing updated.'));
        }

        return $this->engineReaction(2, ['show_message' => true], __tr('Something went wrong on server.'));
    }

    /**
     * Get Timezone list
     *
     * @return array
     *---------------------------------------------------------------- */
    protected function getTimeZone()
    {
        $timezoneCollection = [];
        $timezoneList = timezone_identifiers_list();
        foreach ($timezoneList as $timezone) {
            $timezoneCollection[] = [
                'value' => $timezone,
                'text' => $timezone,
            ];
        }

        return $timezoneCollection;
    }

    /**
     * Process product registration
     *
     * @param  array  $inputData
     * @return void
     *---------------------------------------------------------------- */
    public function processProductRegistration($inputData = [])
    {
        return $this->processConfigurationsStore('product_registration', [
            'product_registration' => [
                'registration_id' => array_get($inputData, 'registration_id', ''),
                'email' => array_get($inputData, 'your_email', ''),
                'licence' => array_get($inputData, 'licence_type', ''),
                'registered_at' => now(),
                'signature' => sha1(
                    array_get($_SERVER, 'HTTP_HOST', '') .
                        array_get($inputData, 'registration_id', '')
                ),
            ],
        ]);
    }

    /**
     * Process product registration removal
     *
     * @return void
     *---------------------------------------------------------------- */
    public function processProductRegistrationRemoval()
    {
        return $this->processConfigurationsStore('product_registration', [
            'product_registration' => [
                'registration_id' => '',
                'email' => '',
                'licence' => '',
                'registered_at' => now(),
                'signature' => '',
            ],
        ]);
    }

    /**
     * Prepare data for mobile app
     *
     * @return array
     */
    public function mobileAppData()
    {
        return [
            'creditPackages' => $this->creditPackageRepository->fetchItAll([
                'status' => 1
            ])
        ];
    }


    /**
     * process Store Custom Fields
     *
     * @return array
     */
    public function processStoreCustomFields($pageType, $inputData)
    {
        $customProfiles = $this->getUserSpecificationData();

        $items = $options = $customProfilesStoreData = [];

        if (isset($inputData['type']) && $inputData['type'] == 'items') {
            if (isset($inputData['items']['key'])) {
                $uniqueId = $inputData['items']['key'];
            } else {
                $uniqueId = uniqid();
            }

            if(isset($inputData['items']['input_type']) && $inputData['items']['input_type'] == 'random'){
                return $this->engineResponse(2, null, __tr('Select input type.'));
            }

            if (isset($inputData['items'])) {
                $items = [];
                $items[$uniqueId] = [
                    'name' => $inputData['items']['name'],
                    'input_type' => $inputData['items']['input_type'],
                ];
                $inputData['items'] = $items;
            } else {
                return $this->engineResponse(2, null, __tr('Something went wrong.'));
            }

            if (!isset($customProfiles['groups'][$inputData['title']]['items'])) {
                $customProfiles['groups'][$inputData['title']]['items'] = [];
            }


            $customProfiles['groups'][$inputData['title']]['items'] = arrayExtend($customProfiles['groups'][$inputData['title']]['items'], $inputData['items']);

            $customProfilesStoreData[] = [
                'name' => 'custom_profiles',
                'value' => json_encode($customProfiles),
                'data_type' => 4
            ];
        }
        if (isset($inputData['type']) && $inputData['type'] == 'options') {

            $items[$inputData['itemName']] = [
                'name' => $inputData['items']['name'],
                'input_type' => $inputData['items']['input_type'],
            ];

            $options = $newlyArray = [];
            if (isset($inputData['options'])) {

                foreach ($inputData['options'] as $optionKey => $optionValue) {
                    if (!__isEmpty($optionValue['optionKey'])) {
                        $options[$optionValue['optionKey']] = $optionValue['option'];
                    } else {
                        $optionUid = uniqid();
                        $options[$optionUid] = $optionValue['option'];
                    }
                }
                $newlyArray['items'] = $items;
                $newlyArray['items'][$inputData['itemName']]['options'] = $options;
            }
            $customProfiles['groups'][$inputData['title']] = arrayExtend($customProfiles['groups'][$inputData['title']], $newlyArray);

            $customProfilesStoreData[] = [
                'name' => 'custom_profiles',
                'value' => json_encode($customProfiles),
                'data_type' => 4
            ];
        }

        if (isset($inputData['type']) && $inputData['type'] == 'edit-option') {
            $items[$inputData['itemName']] = [
                'name' => $inputData['items']['name'],
                'input_type' => $inputData['items']['input_type'],
            ];

            $options = $newlyArray = [];
            if (isset($inputData['options'])) {

                foreach ($inputData['options'] as $optionKey => $optionValue) {
                    if (!__isEmpty($optionValue['optionKey'])) {
                        $options[$optionValue['optionKey']] = $optionValue['option'];
                    } else {
                        $optionUid = uniqid();
                        $options[$optionUid] = $optionValue['option'];
                    }
                }
                $newlyArray['items'] = $items;
                $newlyArray['items'][$inputData['itemName']]['options'] = $options;
            }else{
                $newlyArray['items'] = $items;
            }

            $customProfiles['groups'][$inputData['title']]['items'] = array_merge($customProfiles['groups'][$inputData['title']]['items'], $newlyArray['items']);
            // $customProfiles['groups'][$inputData['title']] = array_merge($customProfiles['groups'][$inputData['title']], $newlyArray);

            $customProfilesStoreData[] = [
                'name' => 'custom_profiles',
                'value' => json_encode($customProfiles),
                'data_type' => 4
            ];
        }

        if (isset($inputData['custom_profiles'])) {
            foreach ($inputData['custom_profiles'] as $key => $values) {
                if (isEmpty($values['title'])) return $this->engineResponse(2, null,  __tr('Profile field is required.'));
                $option['groups'][] = [
                    'title' => $values['title'],
                    'icon' => '<i class="fas fa-wrench text-primary"></i>',
                    'items' => $items,
                ];
            }
            $customProfilesData = arrayExtend($customProfiles, $option);
            $customProfilesStoreData[] = [
                'name' => 'custom_profiles',
                'value' => json_encode($customProfilesData),
                'data_type' => 4
            ];
        }

        if (!__isEmpty($customProfilesStoreData) && $this->configurationRepository->storeOrUpdate($customProfilesStoreData)) {
            updateClientModels([
                'items' => $this->getUserSpecificationData()
            ]);
            return $this->engineResponse(1, null, __tr('Custom profile field Update successfully.'));
        } else {
            return $this->engineResponse(2, null, __tr('Nothing to be update.'));
        }
    }

    public function getUserSpecificationData()
    {
        $customProfiles = $this->configurationRepository->fetchByName('custom_profiles');
        if (__isEmpty($customProfiles)) {
            $customProfiles = array();
        }else{
            $customProfiles = json_decode($customProfiles->value, true);
        }
        return $customProfiles;
    }

    /**
     * Get Items Info
     *
     * @return array
     */
    public function getItemsInfo($groupName, $itemPos)
    {
        $customProfiles = $this->getUserSpecificationData();
        if (!__isEmpty($customProfiles) && $itemPos != 'null') {

            $items = $customProfiles['groups'][$groupName];
            $options = $newItems = $newlyArray = [];

            if (isset($items['items'])) {

                $items = $items['items'][$itemPos];


                foreach ([$items] as $itemKey => $item) {
                    $options = [];
                    if (isset($item['options'])) {
                        foreach ($item['options'] as $optionKey => $option) {
                            $options[] = [
                                'option' => $option,
                                'key' => $optionKey
                            ];
                        }
                    }
                    $newItems = [
                        'groupName' => $groupName,
                        'itemName' => $itemPos,
                        'itemData' => [
                            'name' => isset($item['name']) ? $item['name'] : '',
                            'input_type' => isset($item['input_type']) ? $item['input_type'] : "",
                            'inputTypeName' => isset($item['input_type']) ? ucfirst($item['input_type']) : "",
                            'options' => $options,
                        ]
                    ];

                    $newlyArray[] = $newItems;
                }
            }

            updateClientModels([
                'itemValues' => $newlyArray,
                'item' => array_shift($newlyArray)
            ]);
            return $this->engineResponse(1, [
                'customProfiles' => $customProfiles['groups'][$groupName]
            ]);
        } else if ($itemPos == 'null') {
            // $htmlString = $customProfiles['groups'][$groupName]['icon'];

            updateClientModels([
                'groupData' => $customProfiles['groups'][$groupName]
            ]);
        }
        return $this->engineResponse(1, []);
    }

    /**
     * Add sections
     *
     * @return array
     */
    public function processAddSectionFields($inputData)
    {
        $customProfiles = $this->getUserSpecificationData();
        $uniqueId = uniqid();
        $section = $items = [];
        $icon = '<i class="fas fa-wrench text-primary"></i>';

        if (isset($inputData['groupIndex'])) {
            $section['groups'][$inputData['groupIndex']] = [
                'title' => $inputData['title'],
                'icon' => $icon,
                'items' => $items,
                'status' => $inputData['status'],
            ];
        } else {

            $section['groups'][$uniqueId] = [
                'title' => $inputData['title'],
                'icon' => $icon,
                'items' => $items,
                'status' => $inputData['status'],
            ];

        }
        $customProfilesData = arrayExtend($customProfiles, $section);

        $customProfilesStoreData[] = [
            'name' => 'custom_profiles',
            'value' => json_encode($customProfilesData),
            'data_type' => 4
        ];

        if (!__isEmpty($customProfilesStoreData) && $this->configurationRepository->storeOrUpdate($customProfilesStoreData)) {
            updateClientModels([
                'items' => $this->getUserSpecificationData()
            ]);
            return $this->engineResponse(1, null, __tr('Custom profile field Update successfully.'));
        } else {
            return $this->engineResponse(2, null, __tr('Nothing to be update.'));
        }
    }

    /**
     * Process to Delete fields
     *
     * @return array
     */
    public function processToDeleteCustomFields($groupName, $itemPos, $field)
    {
        $customProfiles = $this->getUserSpecificationData();

        if(!isset($customProfiles['groups'][$groupName]['unDeletableKey'])){
            if ($field == 'group') {
                if (isset($customProfiles['groups'][$groupName])) {
                    unset($customProfiles['groups'][$groupName]);
                }
            }
            if ($field == 'item') {
                if (isset($customProfiles['groups'][$groupName]['items'][$itemPos])) {
                    unset($customProfiles['groups'][$groupName]['items'][$itemPos]);
                }
            }

            if ($field == 'options') {
                if (isset($customProfiles['groups'][$groupName]['items'][$itemPos]['options'])) {
                    unset($customProfiles['groups'][$groupName]['items'][$itemPos]['options']);
                }
            }
            $customProfilesStoreData[] = [
                'name' => 'custom_profiles',
                'value' => json_encode($customProfiles),
                'data_type' => 4
            ];

            if (!__isEmpty($customProfilesStoreData) && $this->configurationRepository->storeOrUpdate($customProfilesStoreData)) {
                updateClientModels([
                    'items' => $this->getUserSpecificationData()
                ]);
                return $this->engineResponse(1, null, __tr('Custom profile field deleted successfully.'));
            } else {
                return $this->engineResponse(2, null, __tr('Custom profile field not deleted.'));
            }
        }
        return $this->engineResponse(2, null, __tr('Custom profile field not deleted.'));
    }
}

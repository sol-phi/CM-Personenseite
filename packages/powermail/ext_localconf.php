<?php

use In2code\Powermail\Utility\ConfigurationUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use In2code\Powermail\Controller\FormController;
use In2code\Powermail\Hook\CreateMarker;
use In2code\Powermail\Tca\EvaluateEmail;
use In2code\Powermail\Eid\GetLocationEid;
use In2code\Powermail\Tca\ShowFormNoteIfNoEmailOrNameSelected;
use In2code\Powermail\Tca\Marker;
use In2code\Powermail\Tca\ShowFormNoteEditForm;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(function () {

    /**
     * Enable caching for show action in form controller
     */
    $uncachedFormActions = 'form';
    if (ConfigurationUtility::isEnableCachingActive()) {
        $uncachedFormActions = '';
    }
    $uncachedFormActions .= ', create, confirmation, optinConfirm, disclaimer';

    /**
     * Include Frontend Plugins for Powermail
     */
    ExtensionUtility::configurePlugin(
        'Powermail',
        'Pi1',
        [
            FormController::class =>
                'form, create, confirmation, optinConfirm, disclaimer'
        ],
        [
            FormController::class => $uncachedFormActions
        ]
    );

    ExtensionUtility::configurePlugin(
        'Powermail',
        'Pi5',
        [
            FormController::class => 'marketing'
        ],
        [
            FormController::class => 'marketing'
        ]
    );

    /**
     * Hook for initially filling the marker field in backend
     */
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
        CreateMarker::class;

    /**
     * JavaScript evaluation of TCA fields
     */
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][EvaluateEmail::class] =
        'EXT:powermail/Classes/Tca/EvaluateEmail.php';

    /**
     * eID to get location from geo coordinates
     */
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['powermailEidGetLocation'] =
        GetLocationEid::class . '::main';

    /**
     * User field registrations in TCA/FlexForm
     */
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1580037906] = [
        'nodeName' => 'powermailShowFormNoteIfNoEmailOrNameSelected',
        'priority' => 50,
        'class' => ShowFormNoteIfNoEmailOrNameSelected::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1580039839] = [
        'nodeName' => 'powermailMarker',
        'priority' => 50,
        'class' => Marker::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1580065317] = [
        'nodeName' => 'powermailShowFormNoteEditForm',
        'priority' => 50,
        'class' => ShowFormNoteEditForm::class,
    ];

    /**
     * Feature toggle
     * ToDo: remove for TYPO3 v14 compatible version
     */
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['powermailEditorsAreAllowedToSendAttachments'] ??= false;
});

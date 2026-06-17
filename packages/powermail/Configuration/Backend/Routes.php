<?php

use In2code\Powermail\Controller\ModuleController;

return [
    'powermail_list' => [
        'path' => '/powermail/list',
        'target' => ModuleController::class . '::listAction',
    ],
    'powermail_formoverview' => [
        'path' => '/powermail/formoverview',
        'target' => ModuleController::class . '::overviewBeAction',
    ],
    'powermail_reportingform' => [
        'path' => '/powermail/reportingform',
        'target' => ModuleController::class . '::reportingFormBeAction',
    ],
    'powermail_reportingmarketing' => [
        'path' => '/powermail/reportingmarketing',
        'target' => ModuleController::class . '::reportingMarketingBeAction',
    ],
    'powermail_functioncheck' => [
        'path' => '/powermail/functioncheck',
        'target' => ModuleController::class . '::checkBeAction',
    ],
    'powermail_downloadfile' => [
        'path' => '/powermail/downloadfile',
        'target' => ModuleController::class . '::downloadFile',
    ],
];

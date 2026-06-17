<?php
use In2code\Powermail\Domain\Model\Form;
use In2code\Powermail\Domain\Model\Page;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Model\Answer;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function () {

        /**
         * Table description files for localization and allowing powermail tables on pages of type default
         */
        $tables = [
            Form::TABLE_NAME,
            Page::TABLE_NAME,
            Field::TABLE_NAME,
            Mail::TABLE_NAME,
            Answer::TABLE_NAME
        ];

        /**
         * Garbage Collector
         */
        $tgct = TableGarbageCollectionTask::class;
        $tables = [
            Mail::TABLE_NAME,
            Answer::TABLE_NAME
        ];
        foreach ($tables as $table) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][$tgct]['options']['tables'][$table] = [
                'dateField' => 'tstamp',
                'expirePeriod' => 30
            ];
        }

        /**
         * Search with TYPO3 backend search
         *      search for an email: "#mail:senderemail"
         *      search for a form: "#form:contactform"
         */
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['mail'] = 'tx_powermail_domain_model_mail';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['form'] = 'tx_powermail_domain_model_form';
    }
);

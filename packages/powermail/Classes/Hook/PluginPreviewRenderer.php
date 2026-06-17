<?php

declare(strict_types=1);

namespace In2code\Powermail\Hook;

use In2code\Powermail\Domain\Model\Form;
use In2code\Powermail\Domain\Repository\MailRepository;
use In2code\Powermail\Events\BackendPageModulePreviewContentEvent;
use In2code\Powermail\Utility\ConfigurationUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Contains a preview rendering for the powermail page module
 */
class PluginPreviewRenderer extends StandardContentPreviewRenderer
{
    protected ?RecordInterface $record = null;
    protected ?FlexFormFieldValues $flexFormData = null;
    protected ?SiteLanguage $siteLanguage = null;

    protected string $templatePathAndFile = 'EXT:powermail/Resources/Private/Templates/Hook/PluginPreview.html';

    /**
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws NotFoundExceptionInterface
     */
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $this->record = $item->getRecord();
        $this->siteLanguage = $item->getSiteLanguage();
        $this->flexFormData = $this->record->get('pi_flexform');

        if (!$this->flexFormData instanceof FlexFormFieldValues) {
            return 'ERROR';
        }

        $preview = '';
        if (!ConfigurationUtility::isDisablePluginInformationActive()) {
            $preview = match ($this->record->get('CType')) {
                'powermail_pi1' => $this->getPluginInformation('Pi1', $item),
                default => '',
            };
        }

        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $event = $eventDispatcher->dispatch(
            new BackendPageModulePreviewContentEvent($preview, $item)
        );
        return $event->getPreview();
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getPluginInformation(string $pluginName, GridColumnItem $item): string
    {
        $row = $this->record?->toArray();
        $cePid = $this->record?->getPid() ?? 0;

        $flexFormSheets = $this->flexFormData?->getSheets();

        /** @var LazyRecordCollection $flexFormMain */
        $flexFormMain = $flexFormSheets['main']['settings']['flexform']['main']['form'];
        $formUid = $flexFormMain->offsetGet(0)->toArray()['uid'];

        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $viewFactoryData = GeneralUtility::makeInstance(
            ViewFactoryData::class,
            null,
            null,
            null,
            $this->templatePathAndFile,
            $GLOBALS['TYPO3_REQUEST'],
        );
        $view = $viewFactory->create($viewFactoryData);

        $view->assignMultiple(
            [
                'row' => $row,
                'formUid' => $this->getLocalizedFormUid(
                    $formUid,
                    $this->siteLanguage?->getLanguageId(),
                ),
                'receiverEmail' => $this->getReceiverEmail(),
                'receiverEmailDevelopmentContext' => ConfigurationUtility::getDevelopmentContextEmail(),
                'mails' => $this->getLatestMails($formUid, $cePid),
                'pluginName' => $pluginName,
                'enableMailPreview' => !ConfigurationUtility::isDisablePluginInformationMailPreviewActive(),
                'form' => $this->getFormTitleByUid($formUid),
            ]
        );
        return $view->render();
    }

    /**
     * Get latest three emails to this form
     */
    protected function getLatestMails(int $formUid, int $pid): QueryResultInterface
    {
        /** @var MailRepository $mailRepository */
        $mailRepository = GeneralUtility::makeInstance(MailRepository::class);
        return $mailRepository->findLatestByFormAndPage(
            $formUid,
            $pid
        );
    }

    /**
     * Get receiver mail
     */
    protected function getReceiverEmail(): string
    {
        $receiverFlexForm = $this->flexFormData?->getSheets()['receiver'];
        $receiver = $receiverFlexForm['settings']['flexform']['receiver']['email'] ?? '';
        if (
            isset($receiverFlexForm['settings']['flexform']['receiver']['type']) &&
            isset($receiverFlexForm['settings']['flexform']['receiver']['fe_group']) &&
            (int)$receiverFlexForm['settings']['flexform']['receiver']['type'] === 1
        ) {
            $receiver = 'Frontenduser Group '
                . (int)$receiverFlexForm['settings']['flexform']['receiver']['fe_group'] ?? 0;
        }

        if (
            isset($receiverFlexForm['settings']['flexform']['receiver']['type']) &&
            isset($receiverFlexForm['settings']['flexform']['receiver']['predefinedemail']) &&
            (int)$receiverFlexForm['settings']['flexform']['receiver']['type'] === 2) {
            $receiver = 'Predefined "'
                . (int)$receiverFlexForm['settings']['flexform']['receiver']['predefinedemail'] . '"';
        }

        return $receiver ?? '';
    }

    /**
     * Get form title from uid
     *
     * @param int $uid Form uid
     */
    protected function getFormTitleByUid(int $uid): string
    {
        $uid = $this->getLocalizedFormUid($uid, $this->getSysLanguageUid());
        $row = BackendUtilityCore::getRecord(Form::TABLE_NAME, $uid, 'title', '', false);
        return $row['title'] ?? '';
    }

    /**
     * Get form uid of a localized form
     */
    protected function getLocalizedFormUid(int $uid, ?int $sysLanguageUid): int
    {
        if ($sysLanguageUid !== null && $sysLanguageUid > 0) {
            $row = BackendUtilityCore::getRecordLocalization(
                Form::TABLE_NAME,
                $uid,
                $sysLanguageUid
            );
            if ($row && !empty($row[0]['uid'])) {
                $uid = (int)$row[0]['uid'];
            }
        }

        return $uid;
    }

    /**
     * Get current sys_language_uid from page content
     */
    protected function getSysLanguageUid(): int
    {
        if ($this->siteLanguage instanceof SiteLanguage) {
            return $this->siteLanguage->getLanguageId();
        }

        return 0;
    }
}

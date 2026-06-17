<?php

declare(strict_types=1);

namespace In2code\Powermail\Condition;

use In2code\Powermail\Utility\DatabaseUtility;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\ExpressionLanguage\RequestWrapper;

/**
 * Class PowermailConditionFunctionsProvider
 * to provide new functions in TypoScript conditions
 */
class PowermailConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return array|ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            $this->isPowermailPluginOnCurrentPageFunction(),
            $this->isPowermailSubmittedFunction(),
        ];
    }

    /**
     * Check if pluginname is anywhere on this page with a new function for conditions: isPowermailOnCurrentPage()
     *
     * Example usages:
     *      [isPowermailOnCurrentPage()] for tt_content.list_type=powermail_pi1 or
     *      [isPowermailOnCurrentPage(['powermail_pi1', 'powermail_pi1'])] for both plugins
     */
    protected function isPowermailPluginOnCurrentPageFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'isPowermailOnCurrentPage',
            function (): void {
                // Not implemented, we only use the evaluator
            },
            function (array $existingVariables, array $plugins = ['powermail_pi1']): bool {
                return $this->isPluginExistingOnCurrentPageInCurrentLanguage(
                    $plugins,
                    $existingVariables['request']
                );
            }
        );
    }

    /**
     * Check if powermail form was just submitted - with a new function: isPowermailSubmitted()
     *
     * Example usage:
     *      [isPowermailSubmitted()]
     */
    protected function isPowermailSubmittedFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'isPowermailSubmitted',
            function (): void {
                // Not implemented, we only use the evaluator
            },
            function (array $arguments) {
                $requestArguments = $this->getMergedBodyAndParams($arguments['request']);
                return !empty($requestArguments['action'])
                    && $requestArguments['action'] === 'create'
                    && !empty($requestArguments['mail']['form']);
            }
        );
    }

    /**
     * @param array<string> $plugins
     * @throws \Doctrine\DBAL\Exception
     */
    protected function isPluginExistingOnCurrentPageInCurrentLanguage(array $plugins, RequestWrapper $request): bool
    {
        $listTypes = implode("','", $plugins);
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('tt_content');
        $row = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                'pid=' . $this->getCurrentPageId($request)
                . " and CType in ('" . $listTypes . "') and sys_language_uid="
                . $this->getCurrentLanguageId($request)
            )->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        return !empty($row['uid']);
    }

    private function getCurrentPageId(RequestWrapper $request): int
    {
        if ($request->getPageArguments()?->getPageId()) {
            return $request->getPageArguments()->getPageId();
        }
        return 0;
    }

    private function getCurrentLanguageId(RequestWrapper $request): int
    {
        if ($request->getSiteLanguage()?->getLanguageId()) {
            return $request->getSiteLanguage()->getLanguageId();
        }
        return 0;
    }

    /**
     * @return array<string>
     */
    private function getMergedBodyAndParams(RequestWrapper $request, string $key = 'tx_powermail_pi1'): array
    {
        $parsedBody = $request->getParsedBody()[$key] ?? [];
        $queryParams = $request->getQueryParams()[$key] ?? [];
        return array_merge($queryParams, $parsedBody);
    }
}

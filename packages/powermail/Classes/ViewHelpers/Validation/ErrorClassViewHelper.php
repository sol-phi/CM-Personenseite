<?php

declare(strict_types=1);

namespace In2code\Powermail\ViewHelpers\Validation;

use In2code\Powermail\Domain\Model\Field;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ErrorClassViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('field', Field::class, 'Field', true);
        $this->registerArgument('class', 'string', 'Class name', false, 'error');
    }

    public function render(): string
    {
        /** @var Field $field */
        $field = $this->arguments['field'];
        $validationResults = $this->getRequest()?->getAttribute('extbase')->getOriginalRequestMappingResults();
        $errors = $validationResults->getFlattenedErrors();
        foreach ($errors as $error) {
            /** @var Error $singleError */
            foreach ((array)$error as $singleError) {
                if (!empty($singleError->getArguments()['marker'])
                    && $field->getMarker() === $singleError->getArguments()['marker']) {
                    return $this->arguments['class'];
                }
            }
        }

        return '';
    }

    protected function getRequest(): ?ServerRequestInterface
    {
        $request = null;
        if ($this->renderingContext?->hasAttribute(ServerRequestInterface::class)) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }
        return $request;
    }
}

<?php

declare(strict_types=1);

namespace SimonSchaufi\Autologin\Domain\Model\Renderable;

use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class SetConfirmUrl
{
    public function __construct(
        private readonly ContentObjectRenderer $contentObjectRenderer
    ) {
    }

    /**
     * @param string|null $elementValue submitted value of the element *before post processing*
     * @param array $requestArguments submitted raw request values
     * @return string|null
     */
    public function afterSubmit(
        FormRuntime $formRuntime,
        RenderableInterface $renderable,
        $elementValue,
        array $requestArguments = []
    ): ?string {
        $identifier = $renderable->getIdentifier();
        if ($identifier === 'confirmurl') {
            $verifyPid = $requestArguments['verifypid'];
            $uniqueHash = $formRuntime->getFormState()?->getFormValue('uniquehash');
            $elementValue = $this->contentObjectRenderer->typoLink_URL([
                'parameter' => 't3://page?uid=' . $verifyPid,
                'additionalParams' => '&uniquehash=' . $uniqueHash,
                'forceAbsoluteUrl' => true,
            ]);
        }

        return $elementValue;
    }
}

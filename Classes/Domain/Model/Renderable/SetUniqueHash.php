<?php

declare(strict_types=1);

namespace SimonSchaufi\Autologin\Domain\Model\Renderable;

use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class SetUniqueHash
{
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
        if ($renderable->getIdentifier() === 'uniquehash') {
            return (new Random())->generateRandomHexString(32);
        }

        return $elementValue;
    }
}

<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Smile\GiftSalesRule\Helper\Config as GiftRuleConfig;

/**
 * View Model: Gift Rule
 *
 * @copyright 2020 Smile
 */
class GiftRule implements ArgumentInterface
{
    /**
     * @param GiftRuleConfig $giftRuleConfig Gift rule config
     */
    public function __construct(protected GiftRuleConfig $giftRuleConfig)
    {
    }

    /**
     * Is popup automatic open enabled ?
     */
    public function isAutomaticPopupOpenEnabled(): bool
    {
        return $this->giftRuleConfig->isAutomaticPopupOpenEnabled();
    }
}

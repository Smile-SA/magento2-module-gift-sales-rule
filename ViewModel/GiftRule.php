<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Smile\GiftSalesRule\Helper\Config as GiftRuleConfig;

/**
 * View Model: Gift Rule
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2020 Smile
 */
class GiftRule implements ArgumentInterface
{
    /**
     * @var GiftRuleConfig
     */
    protected $giftRuleConfig;

    /**
     * @param GiftRuleConfig $giftRuleConfig Gift rule config
     */
    public function __construct(GiftRuleConfig $giftRuleConfig)
    {
        $this->giftRuleConfig = $giftRuleConfig;
    }

    /**
     * Is popup automatic open enabled ?
     *
     * @return bool
     */
    public function isAutomaticPopupOpenEnabled(): bool
    {
        return $this->giftRuleConfig->isAutomaticPopupOpenEnabled();
    }
}

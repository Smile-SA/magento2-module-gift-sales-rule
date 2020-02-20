<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json as serializer;
use Magento\SalesRule\Api\Data\RuleInterface;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Class UpdateGiftRuleActions
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class UpdateGiftRuleActions implements ObserverInterface
{
    const QUOTE_ATTRIBUTE = 'quote_';

    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * @var serializer
     */
    protected $serializer;

    /**
     * UpdateGiftRuleActions constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     * @param serializer     $serializer     Serializer
     */
    public function __construct(
        GiftRuleHelper $giftRuleHelper,
        serializer $serializer
    ) {
        $this->giftRuleHelper = $giftRuleHelper;
        $this->serializer = $serializer;
    }

    /**
     * Remove quote condition if it's gift rule type
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer)
    {
        /** @var RuleInterface $rule */
        $rule = $observer->getRule();

        if ($this->giftRuleHelper->isGiftRule($rule)) {
            if ($rule->getActions()) {
                $actions = $rule->getActions()->asArray();
                if (isset($actions['conditions'])) {
                    foreach ($actions['conditions'] as $index => $condition) {
                        if (strpos($condition['attribute'], self::QUOTE_ATTRIBUTE) !== false) {
                            // Remove quote condition for gift rule.
                            unset($actions['conditions'][$index]);
                        }
                    }

                    $rule->setActionsSerialized($this->serializer->serialize($actions));
                }
            }
        }
    }
}

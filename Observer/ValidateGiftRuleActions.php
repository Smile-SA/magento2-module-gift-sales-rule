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
namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Rule;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Class ValidateGiftRuleActions
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2020 Smile
 */
class ValidateGiftRuleActions implements ObserverInterface
{
    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * ValidateGiftRuleActions constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(GiftRuleHelper $giftRuleHelper)
    {
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Skip actions validation if the rule is a gift sales rule.
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer)
    {
        /** @var Rule $rule */
        $rule = $observer->getEvent()->getData('rule');
        /** @var DataObject $skipValidationObject */
        $skipValidationObject = $observer->getEvent()->getData('skip_validation_object');
        $skipValidation = $skipValidationObject->getData('skip_validation');

        if (!$skipValidation) {
            $skipValidation = $this->giftRuleHelper->isGiftRule($rule);
        }

        $skipValidationObject->setData('skip_validation', $skipValidation);
    }
}

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
namespace Smile\GiftSalesRule\Plugin\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Class CombinePlugin
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class CombinePlugin
{
    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * CombinePlugin constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(
        GiftRuleHelper $giftRuleHelper
    ) {
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Return true if rule is a gift sales rule
     *
     * @param Combine       $subject Subject
     * @param callable      $proceed Proceed
     * @param AbstractModel $model   Model
     *
     * @return bool
     */
    public function aroundValidate(
        Combine $subject,
        callable $proceed,
        AbstractModel $model
    ) {
        if ($this->giftRuleHelper->isGiftRule($subject->getRule())) {
            if (!$this->giftRuleHelper->isValidGiftRule($subject->getRule(), $model->getQuote())) {
                return false;
            }

            return true;
        }

        return $proceed($model);
    }
}

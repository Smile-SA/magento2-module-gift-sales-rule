<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Plugin\Model\Rule\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule\Condition\Product as Subject;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

class ProductPlugin
{
    /**
     * ProductPlugin constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(
        protected GiftRuleHelper $giftRuleHelper
    ) {
    }

    /**
     * If the item is a gift item, do not validate the condition.
     *
     * @param Subject       $subject subject
     * @param bool          $result  result
     * @param AbstractModel $model   model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterValidate(
        Subject $subject,
        bool $result,
        AbstractModel $model
    ): bool {
        $isGiftItem = false;
        if ($model instanceof AbstractItem) {
            $isGiftItem = $this->giftRuleHelper->isGiftItem($model);
        }

        return $result && !$isGiftItem;
    }
}

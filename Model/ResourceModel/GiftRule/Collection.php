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
namespace Smile\GiftSalesRule\Model\ResourceModel\GiftRule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;

/**
 * GiftRule collection.
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Smile\GiftSalesRule\Model\GiftRule::class,
            \Smile\GiftSalesRule\Model\ResourceModel\GiftRule::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray(GiftRuleInterface::RULE_ID, GiftRuleInterface::RULE_ID);
    }
}

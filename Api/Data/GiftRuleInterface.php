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
namespace Smile\GiftSalesRule\Api\Data;

/**
 * GiftRule interface.
 *
 * @api
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
interface GiftRuleInterface
{
    const TABLE_NAME             = 'smile_gift_salesrule';
    const RULE_ID                = 'rule_id';
    const MAXIMUM_NUMBER_PRODUCT = 'maximum_number_product';
    const PRICE_RANGE            = 'price_range';

    /**
     * Rule type actions
     */
    const OFFER_PRODUCT                 = 'offer_product';
    const OFFER_PRODUCT_PER_PRICE_RANGE = 'offer_product_per_price_range';

    /**
     * Get the maximum number product.
     *
     * @return int
     */
    public function getMaximumNumberProduct();

    /**
     * Set the maximum number product.
     *
     * @param int $value
     * @return $this
     */
    public function setMaximumNumberProduct($value);

    /**
     * Get the price range.
     *
     * @return decimal
     */
    public function getPriceRange();

    /**
     * Set the price range.
     *
     * @param decimal $value
     * @return $this
     */
    public function setPriceRange($value);
}

<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Api\Data;

interface GiftRuleInterface
{
    public const TABLE_NAME             = 'smile_gift_salesrule';
    public const RULE_ID                = 'rule_id';
    public const MAXIMUM_NUMBER_PRODUCT = 'maximum_number_product';
    public const PRICE_RANGE            = 'price_range';

    /**
     * Rule type actions
     */
    public const OFFER_PRODUCT                 = 'offer_product';
    public const OFFER_PRODUCT_PER_PRICE_RANGE = 'offer_product_per_price_range';

    /**
     * Get the maximum number product.
     *
     * @return int
     */
    public function getMaximumNumberProduct();

    /**
     * Set the maximum number product.
     *
     * @param int $value Value
     * @return $this
     */
    public function setMaximumNumberProduct($value);

    /**
     * Get the price range.
     *
     * @return float
     */
    public function getPriceRange();

    /**
     * Set the price range.
     *
     * @param float   $value Value
     * @return $this
     */
    public function setPriceRange($value);
}

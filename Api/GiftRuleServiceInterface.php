<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Api;

use Magento\Quote\Model\Quote;

/**
 * Class GiftRuleService
 *
 * @api
 * @copyright 2019 Smile
 */
interface GiftRuleServiceInterface
{
    /**
     * Get available gifts
     *
     * @param Quote $quote Quote
     * @return mixed[]
     *     {gift_rule_id} => [
     *         maximum_number_product => {number}
     *         code => {gift_rule_code}
     *         items => [
     *             {product_id} => [ {product_sku} ]
     *             ...
     *         ]
     *         quote_items => [
     *             {product_id} => {qty}
     *             ...
     *         ]
     *     ]
     */
    public function getAvailableGifts(Quote $quote);

    /**
     * Add gift products
     *
     * @param Quote    $quote      Quote
     * @param array    $products   Products
     * @param string   $identifier Identifier
     * @param int|null $giftRuleId Gift rule id
     * @return void
     */
    public function addGiftProducts(Quote $quote, array $products, string $identifier, ?int $giftRuleId = null);

    /**
     * Replace gift products
     *
     * @param Quote    $quote      Quote
     * @param array    $products   Products
     * @param string   $identifier Identifier
     * @param int|null $giftRuleId Gift rule id
     * @return void
     */
    public function replaceGiftProducts(Quote $quote, array $products, string $identifier, ?int $giftRuleId = null);
}

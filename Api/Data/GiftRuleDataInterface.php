<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Api\Data;

/**
 * GiftRule interface.
 *
 * @api
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 */
interface GiftRuleDataInterface
{
    const RULE_ID                = 'rule_id';
    const CODE                   = 'code';
    const LABEL                  = 'label';
    const QUOTE_ITEMS            = 'quote_items';
    const MAXIMUM_NUMBER_PRODUCT = 'maximum_number_product';
    const PRODUCT_ITEMS          = 'product_items';
    const REST_NUMBER            = 'rest_number';

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
     *
     * @return $this
     */
    public function setMaximumNumberProduct($value);

    /**
     * Get the code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Set the code.
     *
     * @param string $value Value
     *
     * @return $this
     */
    public function setCode($value);

    /**
     * Get the rule id.
     *
     * @return int
     */
    public function getRuleId();

    /**
     * Set the rule id.
     *
     * @param int $value Value
     *
     * @return $this
     */
    public function setRuleId($value);

    /**
     * Get the label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set the label.
     *
     * @param string $value Value
     *
     * @return $this
     */
    public function setLabel($value);

    /**
     * Get the code.
     *
     * @return array
     * [
     *     {product_id} => {qty}
     *     ...
     * ]
     */
    public function getQuoteItems();

    /**
     * Set the code.
     *
     * @param array $items Items
     *
     * @return $this
     */
    public function setQuoteItems($items);

    /**
     * Get the code.
     *
     * @return array
     * [
     *     {product_id} => [ {product_data} ]
     *     ...
     * ]
     */
    public function getProductItems();

    /**
     * Set the code.
     *
     * @param array $items Items
     *
     * @return $this
     */
    public function setProductItems($items);

    /**
     * Get the rest number.
     *
     * @return int
     */
    public function getRestNumber();

    /**
     * Set the rest number.
     *
     * @param int $value Value
     *
     * @return $this
     */
    public function setRestNumber($value);

    /**
     * Populate the object from array values. It is better to use setters instead of the generic setData method.
     *
     * @param array $values Value
     *
     * @return $this
     */
    public function populateFromArray(array $values);
}

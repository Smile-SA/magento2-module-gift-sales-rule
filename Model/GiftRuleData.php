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
namespace Smile\GiftSalesRule\Model;

use Magento\Framework\DataObject;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterface;

/**
 * GiftRuleData model.
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2019 Smile
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class GiftRuleData extends DataObject implements GiftRuleDataInterface
{
    /**
     * @inheritdoc
     */
    public function getMaximumNumberProduct()
    {
        return $this->getData(self::MAXIMUM_NUMBER_PRODUCT);
    }

    /**
     * @inheritdoc
     */
    public function setMaximumNumberProduct($maximumNumberProduct)
    {
        return $this->setData(self::MAXIMUM_NUMBER_PRODUCT, $maximumNumberProduct);
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritdoc
     */
    public function setCode($value)
    {
        return $this->setData(self::CODE, $value);
    }

    /**
     * @inheritdoc
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRuleId($value)
    {
        return $this->setData(self::RULE_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @inheritdoc
     */
    public function setLabel($value)
    {
        return $this->setData(self::LABEL, $value);
    }

    /**
     * @inheritdoc
     */
    public function getProductItems()
    {
        return $this->getData(self::PRODUCT_ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function setProductItems($items)
    {
        return $this->setData(self::PRODUCT_ITEMS, $items);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteItems()
    {
        return $this->getData(self::QUOTE_ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteItems($items)
    {
        return $this->setData(self::QUOTE_ITEMS, $items);
    }

    /**
     * @inheritdoc
     */
    public function getRestNumber()
    {
        return $this->getData(self::REST_NUMBER);
    }

    /**
     * @inheritdoc
     */
    public function setRestNumber($value)
    {
        return $this->setData(self::REST_NUMBER, $value);
    }

    /**
     * @inheritdoc
     */
    public function populateFromArray(array $values)
    {
        $this->setLabel($values['label']);
        $this->setMaximumNumberProduct($values['maximum_number_product']);
        $this->setRestNumber($values['rest_number']);
        $this->setQuoteItems($values['quote_items']);
        $this->setProductItems($values['product_items']);
        $this->setCode($values['code']);
        $this->setRuleId($values['rule_id']);

        return $this;
    }
}

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

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;

/**
 * GiftRule model.
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class GiftRule extends AbstractModel implements GiftRuleInterface, IdentityInterface
{
    const CACHE_TAG = 'smile_gift_sales_rule_gift_rule';

    /**
     * @var string
     */
    protected $_eventPrefix = 'smile_gift_sales_rule_gift_rule';

    /**
     * @var string
     */
    protected $_eventObject = 'smile_gift_sales_rule_gift_rule';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumNumberProduct()
    {
        return $this->getData(self::MAXIMUM_NUMBER_PRODUCT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaximumNumberProduct($maximumNumberProduct)
    {
        return $this->setData(self::MAXIMUM_NUMBER_PRODUCT, $maximumNumberProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceRange()
    {
        return $this->getData(self::PRICE_RANGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceRange($priceRange)
    {
        return $this->setData(self::PRICE_RANGE, $priceRange);
    }

    /**
     * Populate the object from array values
     * It is better to use setters instead of the generic setData method
     *
     * @param array $values values
     *
     * @return GiftRule
     */
    public function populateFromArray(array $values)
    {
        $this->setMaximumNumberProduct($values['maximum_number_product']);
        $this->setPriceRange($values['price_range']);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \Smile\GiftSalesRule\Model\ResourceModel\GiftRule::class
        );
    }
}

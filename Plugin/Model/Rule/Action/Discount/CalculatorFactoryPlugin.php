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
namespace Smile\GiftSalesRule\Plugin\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;

/**
 * Class CalculatorFactoryPlugin
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class CalculatorFactoryPlugin
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $classByType = [
        GiftRuleInterface::OFFER_PRODUCT => 'Smile\GiftSalesRule\Model\Rule\Action\Discount\OfferProduct',
        GiftRuleInterface::OFFER_PRODUCT_PER_PRICE_RANGE
         => 'Smile\GiftSalesRule\Model\Rule\Action\Discount\OfferProductPerPriceRange',
    ];

    /**
     * CalculatorFactoryPlugin constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Add gift sales rule model
     *
     * @param CalculatorFactory $subject
     * @param \Closure          $proceed
     * @param                   $type
     *
     * @return mixed
     */
    public function aroundCreate(
        CalculatorFactory $subject,
        \Closure $proceed,
        $type
    ) {
        if (isset($this->classByType[$type])) {
            return $this->_objectManager->create($this->classByType[$type]);
        }

        return $proceed($type);
    }
}

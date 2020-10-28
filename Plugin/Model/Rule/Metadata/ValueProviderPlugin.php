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
namespace Smile\GiftSalesRule\Plugin\Model\Rule\Metadata;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\GiftRuleRepositoryInterface;
use Smile\GiftSalesRule\Model\GiftRuleFactory;

/**
 * Add gift sales rule
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class ValueProviderPlugin
{
    /**
     * Gift rule repository
     *
     * @var GiftRuleRepositoryInterface
     */
    protected $giftRuleRepository;

    /**
     * Gift rule factory
     *
     * @var GiftRuleFactory
     */
    protected $giftRuleFactory;

    /**
     * UpdateRuleDataObserver constructor.
     *
     * @param GiftRuleRepositoryInterface $giftRuleRepository Gift rule repository
     * @param GiftRuleFactory             $giftRuleFactory    Gift rule factory
     */
    public function __construct(
        GiftRuleRepositoryInterface $giftRuleRepository,
        GiftRuleFactory $giftRuleFactory
    ) {
        $this->giftRuleRepository = $giftRuleRepository;
        $this->giftRuleFactory    = $giftRuleFactory;
    }

    /**
     * Add gift sales rule label with rule type actions
     *
     * @param ValueProvider $subject Subject
     * @param array         $result  Result
     * @param Rule          $rule    Rule
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMetadataValues(
        ValueProvider $subject,
        $result,
        Rule $rule
    ) {
        $extensionAttributes = $rule->getExtensionAttributes();

        $result['actions']['children']['simple_action']['arguments']['data']['config']['options'][] = [
            'label' => __('to offer product'),
            'value' => GiftRuleInterface::OFFER_PRODUCT,
        ];

        $result['actions']['children']['simple_action']['arguments']['data']['config']['options'][] = [
            'label' => __('to offer product per price range'),
            'value' => GiftRuleInterface::OFFER_PRODUCT_PER_PRICE_RANGE,
        ];

        $result['actions']['children']['maximum_number_product']['arguments']['data']['config'] = [
            'value'         => $extensionAttributes
                ? $extensionAttributes['gift_rule'][GiftRuleInterface::MAXIMUM_NUMBER_PRODUCT]
                : '',
            'componentType' => Field::NAME,
            'formElement'   => Input::NAME,
        ];

        $result['actions']['children']['price_range']['arguments']['data']['config'] = [
            'value'         => $extensionAttributes
                ? $extensionAttributes['gift_rule'][GiftRuleInterface::PRICE_RANGE]
                : '',
            'componentType' => Field::NAME,
            'formElement'   => Input::NAME,
        ];

        return $result;
    }
}

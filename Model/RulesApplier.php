<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\RulesApplier as BaseRulesApplier;
use Magento\SalesRule\Model\Utility;

/**
 * Model Rules Applier
 *
 * @copyright 2020 Smile
 */
class RulesApplier extends BaseRulesApplier
{
    /**
     * Is a private parent property.
     */
    protected ChildrenValidationLocator $childrenValidationLocator;

    /**
     * Is a private parent property.
     */
    protected CalculatorFactory $calculatorFactory;

    /**
     * RulesApplier constructor.
     *
     * @param CalculatorFactory              $calculatorFactory         calculator factory
     * @param ManagerInterface               $eventManager              event manager
     * @param Utility                        $utility                   utility
     * @param ChildrenValidationLocator      $childrenValidationLocator children validation locator
     */
    public function __construct(
        CalculatorFactory $calculatorFactory,
        ManagerInterface $eventManager,
        Utility $utility,
        ChildrenValidationLocator $childrenValidationLocator
    ) {
        parent::__construct($calculatorFactory, $eventManager, $utility, $childrenValidationLocator);
        $this->calculatorFactory = $calculatorFactory;
        $this->childrenValidationLocator = $childrenValidationLocator;
    }

    /**
     * Apply rules to current order item.
     *
     * Edit: Add an event skip actions validation for gift rule.
     *
     * The last parameter is not typed on purpose: it is a coupon code (string) before Magento 2.4.7,
     * and a list of coupon codes since Magento 2.4.7.
     *
     * @inheritdoc
     * @param string|string[] $couponCodes
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyRules($item, $rules, $skipValidation, $couponCodes = [])
    {
        $address = $item->getAddress();
        $appliedRuleIds = [];
        /* @var $rule Rule */
        foreach ($rules as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            // Edit Smile: Dispatch an event to be able to edit the actions skip validation.
            $dataObject = new DataObject(['skip_validation' => $skipValidation]);
            $this->_eventManager->dispatch(
                'sales_rules_skip_actions_validation',
                ['skip_validation_object' => $dataObject, 'rule' => $rule]
            );
            $skipValidationForGift = $dataObject->getData('skip_validation');
            if (!$skipValidation && !$rule->getActions()->validate($item) && !$skipValidationForGift) {
                // Edit Smile End.
                if (!$this->childrenValidationLocator->isChildrenValidationRequired($item)) {
                    continue;
                }
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }

            $this->applyRule($item, $rule, $address, $couponCodes);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        return $appliedRuleIds;
    }
}

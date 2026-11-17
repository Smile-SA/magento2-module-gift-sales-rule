<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\Rule;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

class PrepareFormActions implements ObserverInterface
{
    /**
     * PrepareFormActionsObserver constructor.
     *
     * @param Registry        $registry       Registry
     * @param GiftRuleHelper  $giftRuleHelper Gift rule helper
     */
    public function __construct(
        protected Registry $registry,
        protected GiftRuleHelper $giftRuleHelper
    ) {
    }

    /**
     * Change fieldset legend
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer): void
    {
        if ($this->getCurrentSalesRule() && $this->giftRuleHelper->isGiftRule($this->getCurrentSalesRule())) {
            /** @var \Magento\Framework\Data\Form $form */
            $form = $observer->getData('form');
            /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
            $fieldset = $form->getElement('actions_fieldset');

            $fieldset->setData('legend', __('Select free product:'));
        }
    }

    /**
     * Get current sales rule
     */
    protected function getCurrentSalesRule(): ?Rule
    {
        return $this->registry->registry(RegistryConstants::CURRENT_SALES_RULE);
    }
}

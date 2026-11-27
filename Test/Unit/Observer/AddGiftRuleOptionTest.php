<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;
use Smile\GiftSalesRule\Observer\AddGiftRuleOption;
use stdClass;

/**
 * The gift_rule option must only be trusted when the gift rule service adds the product.
 */
class AddGiftRuleOptionTest extends TestCase
{
    private GiftRuleHelper $giftRuleHelper;
    private AddGiftRuleOption $observer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        // Real helper (for its internal flag), without its framework dependencies.
        $this->giftRuleHelper = (new ReflectionClass(GiftRuleHelper::class))->newInstanceWithoutConstructor();
        $this->observer = new AddGiftRuleOption($this->giftRuleHelper);
    }

    /**
     * A gift_rule sent in the add-to-cart request is ignored.
     */
    public function testGiftRuleFromRequestIsIgnored(): void
    {
        $transport = $this->runObserver('12');

        $this->assertArrayNotHasKey('gift_rule', $transport->options);
    }

    /**
     * A gift_rule different from the one being added by the service is ignored.
     */
    public function testGiftRuleOfAnotherRuleIsIgnored(): void
    {
        $this->giftRuleHelper->setGiftRuleIdBeingAdded(5);

        $transport = $this->runObserver('12');

        $this->assertArrayNotHasKey('gift_rule', $transport->options);
    }

    /**
     * The gift_rule of the product added by the service is kept.
     */
    public function testGiftRuleAddedByServiceIsKept(): void
    {
        $this->giftRuleHelper->setGiftRuleIdBeingAdded(12);

        $transport = $this->runObserver('12');

        $this->assertSame('12', $transport->options['gift_rule']);
    }

    /**
     * Run the observer for a buy request holding the given gift_rule.
     */
    private function runObserver(string $giftRule): object
    {
        $transport = new stdClass();
        $transport->options = [];

        $event = new Event(['buy_request' => new DataObject(['gift_rule' => $giftRule]), 'transport' => $transport]);
        $this->observer->execute(new Observer(['event' => $event]));

        return $transport;
    }
}

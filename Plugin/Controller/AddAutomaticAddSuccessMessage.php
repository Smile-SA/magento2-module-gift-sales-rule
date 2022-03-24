<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Plugin\Controller;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Smile\GiftSalesRule\Model\Context\GiftRule as GiftRuleContext;

/**
 * Plugin AddAutomaticAddSuccessMessage: Add the success message of adding automatically gift product.
 *
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 */
class AddAutomaticAddSuccessMessage
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ManagerInterface
     */
    protected $giftRuleContext;

    /**
     * @param ManagerInterface $messageManager  Message manager
     * @param GiftRuleContext  $giftRuleContext Gift rule context
     */
    public function __construct(
        ManagerInterface $messageManager,
        GiftRuleContext $giftRuleContext
    ) {
        $this->messageManager  = $messageManager;
        $this->giftRuleContext = $giftRuleContext;
    }

    /**
     * Add the success message of adding automatically gift product.
     *
     * @param ActionInterface $subject Subject
     * @param Http|Page       $result  Result
     * @return Http|Page
     */
    public function afterExecute(
        ActionInterface $subject,
        $result
    ) {
        $giftAddedAutomatically = $this->giftRuleContext->getGiftAddedAutomaticallyCount();
        if ($giftAddedAutomatically) {
            $message = __('A gift product was added to your cart.');
            if ($giftAddedAutomatically > 1) {
                $message = __('Gift products were added to your cart.');
            }

            $this->messageManager->addSuccessMessage($message);
        }

        return $result;
    }
}

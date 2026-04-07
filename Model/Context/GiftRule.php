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

namespace Smile\GiftSalesRule\Model\Context;

/**
 * GiftRule context.
 *
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 */
class GiftRule
{
    /**
     * @var bool
     */
    protected $giftAddedAutomaticallyCount;

    /**
     * Get gift added automatically count.
     *
     * @return int|null
     */
    public function getGiftAddedAutomaticallyCount(): ?int
    {
        return $this->giftAddedAutomaticallyCount;
    }

    /**
     * Set gift added automatically count.
     *
     * @param int $giftAddedAutomaticallyCount
     * @return GiftRule
     */
    public function setGiftAddedAutomaticallyCount(int $giftAddedAutomaticallyCount): GiftRule
    {
        $this->giftAddedAutomaticallyCount = $giftAddedAutomaticallyCount;

        return $this;
    }
}

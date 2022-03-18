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
namespace Smile\GiftSalesRule\Model\Quote\Totals\Item;

use Smile\GiftSalesRule\Api\Data\GiftRuleTotalsItemInterface;
use Magento\Framework\DataObject;

/**
 * GiftRule totals item model.
 *
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 */
class GiftRuleTotalsItem extends DataObject implements GiftRuleTotalsItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function setImageSrc(?string $src): GiftRuleTotalsItemInterface
    {
        return $this->setData(self::IMAGE_SRC, $src);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageSrc(): ?string
    {
        return $this->getData(self::IMAGE_SRC);
    }

    /**
     * {@inheritdoc}
     */
    public function setImageAlt(?string $alt): GiftRuleTotalsItemInterface
    {
        return $this->setData(self::IMAGE_ALT, $alt);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageAlt(): ?string
    {
        return $this->getData(self::IMAGE_ALT);
    }

    /**
     * {@inheritdoc}
     */
    public function setImageWidth(?string $width): GiftRuleTotalsItemInterface
    {
        return $this->setData(self::IMAGE_WIDTH, $width);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageWidth(): ?string
    {
        return $this->getData(self::IMAGE_WIDTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setImageHeight(?string $height): GiftRuleTotalsItemInterface
    {
        return $this->setData('image_height', $height);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageHeight(): ?string
    {
        return $this->getData(self::IMAGE_HEIGHT);
    }
}

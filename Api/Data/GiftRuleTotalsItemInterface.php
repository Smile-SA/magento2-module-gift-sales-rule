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
namespace Smile\GiftSalesRule\Api\Data;

/**
 * GiftRule totals item interface.
 *
 * @api
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 */
interface GiftRuleTotalsItemInterface
{
    const IMAGE_SRC    = 'image_src';
    const IMAGE_ALT    = 'image_alt';
    const IMAGE_WIDTH  = 'image_width';
    const IMAGE_HEIGHT = 'image_height';

    /**
     * Set image src.
     *
     * @param string|null $src
     * @return $this
     */
    public function setImageSrc(?string $src): GiftRuleTotalsItemInterface;

    /**
     * Get image src.
     *
     * @return string|null
     */
    public function getImageSrc(): ?string;

    /**
     * Set image alt.
     *
     * @param string|null $alt
     * @return $this
     */
    public function setImageAlt(?string $alt): GiftRuleTotalsItemInterface;

    /**
     * Get image alt.
     *
     * @return string|null
     */
    public function getImageAlt(): ?string;

    /**
     * Set image width.
     *
     * @param string|null $width
     * @return $this
     */
    public function setImageWidth(?string $width): GiftRuleTotalsItemInterface;

    /**
     * Get image width.
     *
     * @return string|null
     */
    public function getImageWidth(): ?string;

    /**
     * Set image height
     *
     * @param string|null $height
     * @return $this
     */
    public function setImageHeight(?string $height): GiftRuleTotalsItemInterface;

    /**
     * Get image height.
     *
     * @return string|null
     */
    public function getImageHeight(): ?string;
}

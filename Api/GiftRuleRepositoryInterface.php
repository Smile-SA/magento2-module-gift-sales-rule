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
namespace Smile\GiftSalesRule\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleSearchResultsInterface;

/**
 * GiftRule repository interface.
 *
 * @api
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
interface GiftRuleRepositoryInterface
{
    /**
     * Get a giftrule by ID.
     *
     * @param int $entityId
     * @return GiftRuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get the giftrules matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return GiftRuleSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null);

    /**
     * Save the GiftRule.
     *
     * @param GiftRuleInterface $giftRule
     * @return GiftRuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(GiftRuleInterface $giftRule);

    /**
     * Delete a giftrule by ID.
     *
     * @param int $entityId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);
}

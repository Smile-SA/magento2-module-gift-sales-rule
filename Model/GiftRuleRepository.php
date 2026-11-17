<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Data\Collection\AbstractDb as AbstractCollection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterfaceFactory;
use Smile\GiftSalesRule\Api\Data\GiftRuleSearchResultsInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleSearchResultsInterfaceFactory;
use Smile\GiftSalesRule\Api\GiftRuleRepositoryInterface;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;
use Smile\GiftSalesRule\Model\ResourceModel\GiftRule as GiftRuleResource;
use Smile\GiftSalesRule\Model\ResourceModel\GiftRule\CollectionFactory as GiftRuleCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftRuleRepository implements GiftRuleRepositoryInterface
{
    protected mixed $objectFactory;

    protected mixed $objectCollectionFactory;

    protected mixed $objectSearchResultsFactory;

    protected array $cacheById = [];

    protected array $cacheByIdentifier = [];

    /**
     * GiftRuleRepository constructor.
     *
     * @param CollectionProcessor                   $collectionProcessor        Collection processor
     * @param GiftRuleInterfaceFactory              $objectFactory              Gift rule interface factory
     * @param GiftRuleResource                      $objectResource             Gift rule resource
     * @param GiftRuleCollectionFactory             $objectCollectionFactory    Gift rule collection factory
     * @param GiftRuleSearchResultsInterfaceFactory $objectSearchResultsFactory Search results factory
     * @param CacheInterface                        $cache                      Cache interface
     * @param string|null                           $identifierFieldName        Identifier field name
     */
    public function __construct(
        protected CollectionProcessor $collectionProcessor,
        GiftRuleInterfaceFactory $objectFactory,
        protected GiftRuleResource $objectResource,
        GiftRuleCollectionFactory $objectCollectionFactory,
        GiftRuleSearchResultsInterfaceFactory $objectSearchResultsFactory,
        protected CacheInterface $cache,
        protected ?string $identifierFieldName = null
    ) {
        $this->objectFactory              = $objectFactory;
        $this->objectCollectionFactory    = $objectCollectionFactory;
        $this->objectSearchResultsFactory = $objectSearchResultsFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getById($objectId)
    {
        if (!isset($this->cacheById[$objectId])) {
            /** @var \Magento\Framework\Model\AbstractModel $object */
            $object = $this->objectFactory->create();
            $this->objectResource->load($object, $objectId);

            if (!$object->getId()) {
                // Object does not exist.
                throw NoSuchEntityException::singleField('objectId', $objectId);
            }

            $this->cacheById[$object->getId()] = $object;

            if ($this->identifierFieldName != null) {
                $objectIdentifier = $object->getData($this->identifierFieldName);
                $this->cacheByIdentifier[$objectIdentifier] = $object;
            }
        }

        return $this->cacheById[$objectId];
    }

    /**
     * @inheritdoc
     */
    public function getByRule($rule)
    {
        $ruleId = $rule->getData($this->objectResource->getLinkFieldName()) ?: $rule->getId();

        return $this->getById($ruleId);
    }

    /**
     * @inheritdoc
     */
    public function getList(?SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->objectCollectionFactory->create();

        /** @var GiftRuleSearchResultsInterface $searchResults */
        $searchResults = $this->objectSearchResultsFactory->create();

        if ($searchCriteria) {
            $searchResults->setSearchCriteria($searchCriteria);
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        // Load the collection.
        $collection->load();

        // Build the result.
        $searchResults->setTotalCount($collection->getSize());
        /** @var GiftRuleInterface[] $items */
        $items = $collection->getItems();
        $searchResults->setItems($items);

        return $searchResults;
    }

    /**
     * Delete entity
     *
     * @param AbstractModel $object Object
     * @throws CouldNotDeleteException
     */
    public function deleteEntity(AbstractModel $object): bool
    {
        try {
            $this->objectResource->delete($object);

            unset($this->cacheById[$object->getId()]);
            if ($this->identifierFieldName != null) {
                $objectIdentifier = $object->getData($this->identifierFieldName);
                unset($this->cacheByIdentifier[$objectIdentifier]);
            }
        } catch (Exception $e) {
            $msg = new Phrase($e->getMessage());
            throw new CouldNotDeleteException($msg);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(GiftRuleInterface $object)
    {
        /** @var GiftRule $object */
        try {
            $this->objectResource->save($object);

            unset($this->cacheById[$object->getId()]);
            if ($this->identifierFieldName != null) {
                $objectIdentifier = $object->getData($this->identifierFieldName);
                unset($this->cacheByIdentifier[$objectIdentifier]);
            }

            /** Flush gift rule data cached */
            $this->cache->clean([GiftRuleCacheHelper::CACHE_DATA_TAG]);
        } catch (Exception $e) {
            $msg = new Phrase($e->getMessage());
            throw new CouldNotSaveException($msg);
        }

        return $object;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($objectId)
    {
        /** @var GiftRule $object */
        $object = $this->getById($objectId);

        return $this->deleteEntity($object);
    }
}

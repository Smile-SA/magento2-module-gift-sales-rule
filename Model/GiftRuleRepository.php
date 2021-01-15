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
namespace Smile\GiftSalesRule\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Data\Collection\AbstractDb as AbstractCollection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as AbstractResourceModel;
use Magento\Framework\Phrase;
use Smile\GiftSalesRule\Api\GiftRuleRepositoryInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleSearchResultsInterfaceFactory;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterfaceFactory;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;
use Smile\GiftSalesRule\Model\ResourceModel\GiftRule as GiftRuleResource;
use Smile\GiftSalesRule\Model\ResourceModel\GiftRule\CollectionFactory as GiftRuleCollectionFactory;

/**
 * GiftRule repository.
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftRuleRepository implements GiftRuleRepositoryInterface
{
    /** @var CollectionProcessor */
    protected $collectionProcessor;

    /** @var mixed */
    protected $objectFactory;

    /** @var AbstractResourceModel */
    protected $objectResource;

    /** @var mixed */
    protected $objectCollectionFactory;

    /** @var mixed */
    protected $objectSearchResultsFactory;

    /** @var string|null */
    protected $identifierFieldName = null;

    /** @var array */
    protected $cacheById = [];

    /** @var CacheInterface */
    protected $cache;

    /** @var array */
    protected $cacheByIdentifier = [];

    /**
     * GiftRuleRepository constructor.
     *
     * @param CollectionProcessor                   $collectionProcessor        Collection processor
     * @param GiftRuleInterfaceFactory              $objectFactory              Gift rule interface factory
     * @param GiftRuleResource                      $objectResource             Gift rule resource
     * @param GiftRuleCollectionFactory             $objectCollectionFactory    Gift rule collection factory
     * @param GiftRuleSearchResultsInterfaceFactory $objectSearchResultsFactory Gift rule search results interface factory
     * @param CacheInterface                        $cache                      Cache interface
     * @param string|null                           $identifierFieldName        Identifier field name
     */
    public function __construct(
        CollectionProcessor $collectionProcessor,
        GiftRuleInterfaceFactory $objectFactory,
        GiftRuleResource $objectResource,
        GiftRuleCollectionFactory $objectCollectionFactory,
        GiftRuleSearchResultsInterfaceFactory $objectSearchResultsFactory,
        CacheInterface $cache,
        $identifierFieldName = null
    ) {
        $this->collectionProcessor        = $collectionProcessor;
        $this->objectFactory              = $objectFactory;
        $this->objectResource             = $objectResource;
        $this->objectCollectionFactory    = $objectCollectionFactory;
        $this->objectSearchResultsFactory = $objectSearchResultsFactory;
        $this->cache                      = $cache;
        $this->identifierFieldName        = $identifierFieldName;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getByRule($rule)
    {
        $ruleId = $rule->getData($this->objectResource->getLinkFieldName()) ?: $rule->getId();

        return $this->getById($ruleId);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->objectCollectionFactory->create();

        /** @var \Magento\Framework\Api\SearchResults $searchResults */
        $searchResults = $this->objectSearchResultsFactory->create();

        if ($searchCriteria) {
            $searchResults->setSearchCriteria($searchCriteria);
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        // Load the collection.
        $collection->load();

        // Build the result.
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * Delete entity
     *
     * @param AbstractModel $object Object
     *
     * @return boolean
     * @throws CouldNotDeleteException
     */
    public function deleteEntity(AbstractModel $object)
    {
        try {
            $this->objectResource->delete($object);

            unset($this->cacheById[$object->getId()]);
            if ($this->identifierFieldName != null) {
                $objectIdentifier = $object->getData($this->identifierFieldName);
                unset($this->cacheByIdentifier[$objectIdentifier]);
            }
        } catch (\Exception $e) {
            $msg = new Phrase($e->getMessage());
            throw new CouldNotDeleteException($msg);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(GiftRuleInterface $object)
    {
        /** @var AbstractModel $object */
        try {
            $this->objectResource->save($object);

            unset($this->cacheById[$object->getId()]);
            if ($this->identifierFieldName != null) {
                $objectIdentifier = $object->getData($this->identifierFieldName);
                unset($this->cacheByIdentifier[$objectIdentifier]);
            }

            /** Flush gift rule data cached */
            $this->cache->clean(GiftRuleCacheHelper::CACHE_DATA_TAG);
        } catch (\Exception $e) {
            $msg = new Phrase($e->getMessage());
            throw new CouldNotSaveException($msg);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($objectId)
    {
        return $this->deleteEntity($this->getById($objectId));
    }
}

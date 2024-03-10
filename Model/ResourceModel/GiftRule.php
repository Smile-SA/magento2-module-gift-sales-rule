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
namespace Smile\GiftSalesRule\Model\ResourceModel;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\SalesRule\Api\Data\RuleInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;

/**
 * GiftRule resource model.
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class GiftRule extends AbstractDb
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var MetadataPool */
    protected $metadataPool;

    /**
     * GiftRule constructor.
     *
     * @param Context       $context        Context
     * @param EntityManager $entityManager  Entity manage
     * @param MetadataPool  $metadataPool   Metadata poll
     * @param null          $connectionName Connection name
     */
    public function __construct(
        Context       $context,
        EntityManager $entityManager,
        MetadataPool  $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        $connectionName = $this->metadataPool->getMetadata(GiftRuleInterface::class)->getEntityConnectionName();

        return $this->_resources->getConnectionByName($connectionName);
    }

    /**
    * {@inheritdoc}
    */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $objectId = $this->getObjectId($value, $field);

        if ($objectId) {
            $object->beforeLoad($value, $field);
            $this->entityManager->load($object, $objectId);
            $object->afterLoad();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);

        return $this;
    }

    /**
     * Get rule link field name.
     *
     * @return string
     * @throws \Exception
     */
    public function getLinkFieldName()
    {
        return $this->metadataPool->getMetadata(RuleInterface::class)->getLinkField();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            GiftRuleInterface::TABLE_NAME,
            GiftRuleInterface::RULE_ID
        );
    }

    /**
     * Get the id of an object
     *
     * @param mixed       $value Value
     * @param string|null $field Field
     *
     * @return bool|int|string
     */
    protected function getObjectId($value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(GiftRuleInterface::class);
        if ($field === null) {
            $field = $entityMetadata->getIdentifierField();
        }
        $entityId = $value;

        if ($field != $entityMetadata->getIdentifierField()) {
            $field = $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
            $select = $this->getConnection()->select()->from($this->getMainTable())->where($field . '=?', $value);

            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $entityId = count($result) ? $result[0] : false;
        }

        return $entityId;
    }
}

<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class UpgradeSchema
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Product
     */
    protected $_productModel;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * UpgradeSchema constructor.
     * @param Product $productModel
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Product $productModel,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_productModel = $productModel;
        $this->_customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ms_recipe_product'),
                'qty',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'comment' => 'qty products'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('ms_recipe'),
                'writer_id',
                'customer_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'Customer Id'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.12', '<')) {
            $setup->getConnection()
                ->dropForeignKey(
                    $setup->getTable('ms_recipe_store'),
                    $setup->getFkName(
                        $setup->getTable('ms_recipe_store'),
                        'recipe_id',
                        'ms_recipe',
                        'recipe_id'
                    )
                );

            $setup->getConnection()->modifyColumn(
                $setup->getTable('ms_recipe_store'),
                'recipe_id',
                [
                    'type'      => Table::TYPE_INTEGER,
                    'identity'  => false,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => false
                ]
            );

            $setup->getConnection()->dropIndex(
                $setup->getTable('ms_recipe_store'),
                'PRIMARY'
            );

            $setup->getConnection()
                ->addForeignKey(
                    $setup->getFkName(
                        $setup->getTable('ms_recipe_store'),
                        'recipe_id',
                        'ms_recipe',
                        'recipe_id'
                    ),
                    $setup->getTable('ms_recipe_store'),
                    'recipe_id',
                    $setup->getTable('ms_recipe'),
                    'recipe_id',
                    Table::ACTION_CASCADE
                );
        }

        if (version_compare($context->getVersion(), '1.0.13', '<')) {

            $sql = $setup->getConnection()->select()
                ->from($setup->getTable('ms_recipe_writer'));

            $writers = $setup->getConnection()->fetchAll($sql);

            foreach ($writers as $writer) {
                if (!empty($writer['writer_url_key'])) {
                    $existUrlKey = $this->isExistDuplicateUrlKey($setup, $writer['writer_url_key']);
                    if (!$existUrlKey) {
                        continue;
                    }
                }

                $urlKey = $writer['writer_url_key'];
                if (!$urlKey) {
                    $customer = $this->_customerRepository->getById($writer['customer_id']);

                    if ($customer->getId()) {
                        $urlKey = $this->_productModel->formatUrlKey($customer->getFirstname() . '-' . $customer->getLastname());
                    }
                }

                $count = 1;
                do {
                    $newUrlKey = $urlKey;
                    if ($count >= 1) {
                        $newUrlKey = $urlKey . '-' . $count;
                    }

                    $count++;
                    $isDuplicateSaved = $this->isExistUrlKey($setup, $newUrlKey);
                } while ($isDuplicateSaved);

                $writer['writer_url_key'] = $newUrlKey;

                $setup->getConnection()->update(
                    $setup->getTable('ms_recipe_writer'),
                    $writer,
                    ['id = ?'   => (int)$writer['id']]
                );
            }

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable('ms_recipe_writer'),
                    $setup->getIdxName(
                        $setup->getTable('ms_recipe_writer'),
                        ['writer_url_key'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['writer_url_key'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );
        }

        if (version_compare($context->getVersion(), '1.0.14', '<')) {
            $setup->getConnection()
                ->addIndex(
                    $setup->getTable('ms_recipe'),
                    $setup->getIdxName(
                        $setup->getTable('ms_recipe'),
                        ['customer_id', 'url_key'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['customer_id', 'url_key'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );
        }

        $installer = $setup;
        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '1.0.22', '<')) {

            $setup->startSetup();
            $tableName = $setup->getTable('ms_recipe_text');
            if ($setup->getConnection()->isTableExists($tableName) !== true) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Value ID'
                    )
                    ->addColumn(
                        'recipe_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                        ],
                        'Recipe Entity ID'
                    )
                    ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => 0
                    ],
                    'Store ID'
                    )
                    ->addColumn(
                        'meta_key',
                        Table::TYPE_TEXT,
                        null,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Key'
                        ]
                    )
                    ->addColumn(
                        'meta_value',
                        Table::TYPE_TEXT,
                        null,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Value'
                        ]
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'ms_recipe_text',
                            'recipe_id',
                            'ms_recipe',
                            'recipe_id'
                        ),
                        'recipe_id',
                        $installer->getTable('ms_recipe'),
                        'recipe_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            $setup->getTable('ms_recipe_text'),
                            ['recipe_id', 'store_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['recipe_id', 'store_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    )
                    ->setComment('Recipe Entity Text');
                $setup->getConnection()->createTable($table);

            }
            $setup->endSetup();

            $setup->startSetup();
            $tableName = $setup->getTable('ms_recipe_varchar');
            if ($setup->getConnection()->isTableExists($tableName) !== true) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Value ID'
                    )
                    ->addColumn(
                        'recipe_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                        ],
                        'Recipe Entity ID'
                    )
                    ->addColumn(
                        'store_id',
                        Table::TYPE_SMALLINT,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                            'default' => 0
                        ],
                        'Store ID'
                    )
                    ->addColumn(
                        'meta_key',
                        Table::TYPE_TEXT,
                        null,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Key'
                        ]
                    )
                    ->addColumn(
                        'meta_value',
                        Table::TYPE_TEXT,
                        255,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Value'
                        ]
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'ms_recipe_varchar',
                            'recipe_id',
                            'ms_recipe',
                            'recipe_id'
                        ),
                        'recipe_id',
                        $installer->getTable('ms_recipe'),
                        'recipe_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            $setup->getTable('ms_recipe_varchar'),
                            ['recipe_id', 'store_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['recipe_id', 'store_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    )
                    ->setComment('Recipe Entity Varchar');
                $setup->getConnection()->createTable($table);
            }
            $setup->endSetup();

            $setup->startSetup();
            $tableName = $setup->getTable('ms_recipe_filter_varchar');
            if ($setup->getConnection()->isTableExists($tableName) !== true) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Value ID'
                    )
                    ->addColumn(
                        'filter_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                        ],
                        'Recipe Filter Entity ID'
                    )
                    ->addColumn(
                        'store_id',
                        Table::TYPE_SMALLINT,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                            'default' => 0
                        ],
                        'Store ID'
                    )
                    ->addColumn(
                        'meta_key',
                        Table::TYPE_TEXT,
                        null,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Key'
                        ]
                    )
                    ->addColumn(
                        'meta_value',
                        Table::TYPE_TEXT,
                        255,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Value'
                        ]
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'ms_recipe_filter_varchar',
                            'filter_id',
                            'ms_recipe_filter',
                            'id'
                        ),
                        'filter_id',
                        $installer->getTable('ms_recipe_filter'),
                        'id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            $setup->getTable('ms_recipe_filter_varchar'),
                            ['filter_id','store_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['filter_id','store_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    )
                    ->setComment('Recipe Filter Varchar');
                $setup->getConnection()->createTable($table);
            }
            $setup->endSetup();

            $setup->startSetup();
            $tableName = $setup->getTable('ms_recipe_filter_options_varchar');
            if ($setup->getConnection()->isTableExists($tableName) !== true) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'Value ID'
                    )
                    ->addColumn(
                        'filter_option_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                        ],
                        'Recipe Filter Options Entity ID'
                    )
                    ->addColumn(
                        'store_id',
                        Table::TYPE_SMALLINT,
                        null,
                        [
                            'unsigned' => true,
                            'nullable' => false,
                            'default' => 0
                        ],
                        'Store ID'
                    )
                    ->addColumn(
                        'meta_key',
                        Table::TYPE_TEXT,
                        null,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Key'
                        ]
                    )
                    ->addColumn(
                        'meta_value',
                        Table::TYPE_TEXT,
                        255,
                        [
                            ['nullable' => true, 'default' => ''],
                            'comment' => 'Meta Value'
                        ]
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'ms_recipe_filter_options_varchar',
                            'filter_option_id',
                            'ms_recipe_filter_options',
                            'id'
                        ),
                        'filter_option_id',
                        $installer->getTable('ms_recipe_filter_options'),
                        'id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            $setup->getTable('ms_recipe_filter_options_varchar'),
                            ['filter_option_id','store_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['filter_option_id','store_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    )
                    ->setComment('Recipe Filter Options Varchar');
                $setup->getConnection()->createTable($table);
            }
            $setup->endSetup();
        }
    }

    public function isExistDuplicateUrlKey($setup, $urlKey)
    {
        $customerIds = $this->executeQuery($setup, $urlKey);
        if ($customerIds && count($customerIds) > 1) {
            return true;
        }
        return false;
    }

    public function isExistUrlKey($setup, $urlKey)
    {
        $customerIds = $this->executeQuery($setup, $urlKey);
        if ($customerIds && count($customerIds) >= 1) {
            return true;
        }
        return false;
    }

    public function executeQuery($setup, $urlKey)
    {
        $sql = $setup->getConnection()->select()
            ->from($setup->getTable('ms_recipe_writer'),['customer_id'])
            ->where('writer_url_key = ?', $urlKey);

        $customerIds = $setup->getConnection()->fetchCol($sql);

        return $customerIds;
    }
}

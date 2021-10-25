<?php
/**
 * Qsciences
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the End User License Agreement (EULA)
  * that is bundled with this package in the file LICENSE.txt.
  * It is also available through the world-wide-web at this URL:
  * https://qsciences.com/license-agreement.txt
  *
  * @category  Qsciences
  * @package   Qsciences_GraphQl
  * @author    Qsciences Core Team <connect@qsciences.com >
  * @copyright Copyright Qsciences (https://qsciences.com/)
  * @license      https://qsciences.com/license-agreement.txt
  */
declare(strict_types=1);

namespace Qsciences\GraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * StockQty field resolver, used for GraphQL request processing
 */
class StockQty implements ResolverInterface
{
    /**
     * StockQty Contruct Method
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param \Qsciences\GraphQl\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Qsciences\GraphQl\Helper\Data $moduleHelper
    ) {
        $this->_resource = $resource;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_moduleHelper = $moduleHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        //$accessToken = $this->_moduleHelper->getAccessToken();
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $result = [];

        /* @var $product ProductInterface */
        $product   = $value['model'];

        $productId = $product->getId();
        $stockItem = $this->_stockItemRepository->get($productId);
        $manageStock = (int) $stockItem->getManageStock();
        $manageStock = ($manageStock == 1) ? 'Yes' : 'No';
        $result['manage_stock'] = $manageStock;
        $result['qty'] = (int) $stockItem->getQty();

        return $result;
    }
    
}

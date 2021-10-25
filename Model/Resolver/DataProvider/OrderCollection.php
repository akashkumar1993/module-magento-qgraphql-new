<?php
/*******************************************************************************
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2021 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Adobe permits you to use and modify this file
 * in accordance with the terms of the Adobe license agreement
 * accompanying it (see LICENSE_ADOBE_PS.txt).
 * If you have received this file from a source other than Adobe,
 * then your use, modification, or distribution of it
 * requires the prior written permission from Adobe.
 ******************************************************************************/

declare (strict_types = 1);

namespace Qsciences\GraphQl\Model\Resolver\DataProvider;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * OrderCollection data provider
 */
class OrderCollection
{
    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var EntityAttribute
     */
    private $_entityAttribute;
    /**
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param FilterEmulate $widgetFilter
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Attribute $entityAttribute,
        Config $eavConfig,
        FilterEmulate $widgetFilter
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->productRepository = $productRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_entityAttribute = $entityAttribute;
        $this->_eavConfig = $eavConfig;
        $this->widgetFilter = $widgetFilter;
    }

    /**
     * @param string $orderStatus
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData($orderStatus, $createdStartDate, $createdEndDate): array
    {
        $orders = $this->orderCollectionFactory->create();

        $associateAttributeId = $this->_entityAttribute->getIdByCode('customer', 'associate_id');
        $legacyAssociateAttributeId = $this->_entityAttribute->getIdByCode('customer', 'legacy_associate_id');

        $orders->getSelect()->joinLeft(
            array('associate_id_table' => $orders->getTable('customer_entity_varchar')),
            'main_table.customer_id = associate_id_table.entity_id AND associate_id_table.attribute_id = '.$associateAttributeId,
            array('associate_id' => 'value')
        );
        $orders->getSelect()->joinLeft(
            array('legacy_associate_id_table' => $orders->getTable('customer_entity_varchar')),
            'main_table.customer_id = legacy_associate_id_table.entity_id AND legacy_associate_id_table.attribute_id = '.$legacyAssociateAttributeId,
            array('legacy_associate_id' => 'value')
        );

        $orderStatus = strtolower($orderStatus);
        //$orders->addAttributeToFilter('status', ['in' => $orderStatus]);
        $orders->addAttributeToFilter('status', $orderStatus);

        if ($createdStartDate != '') {
            $startDate = date("Y-m-d", strtotime($createdStartDate));
            $orders->addAttributeToFilter('created_at', ['gteq' => $startDate . ' 00:00:00']);

            if ($createdEndDate == '') {
                $createdEndDate = date("Y-m-d");
            }
        }
        if ($createdEndDate != '') {
            $endDate = date("Y-m-d", strtotime($createdEndDate));
            $orders->addAttributeToFilter('created_at', ['lteq' => $endDate . ' 23:59:59']);
        }

        $orderCollectionLoaded = [];
        foreach ($orders as $key => $order) {
            $data = [];
            $associate_id = '';
            $legacy_associate_id = '';
            $customerId = $order->getData('customer_id');
            if ($customerId != '') {
                $associate_id = $order->getData('associate_id');
                $legacy_associate_id = $order->getData('legacy_associate_id');
            }
            $data['associate_id'] = $associate_id;
            $data['legacy_associate_id'] = $legacy_associate_id;
            $data['customer_id'] = $customerId;
            $data['entity_id'] = $order->getData('entity_id');
            $data['increment_id'] = (string) $order->getData('increment_id');

            $items = [];
            $total_pv = 0;
            foreach ($order->getAllItems() as $item) {
                $product = $this->getProductBySku($item->getSku());
                $pv_per_item = 0;
                if ($product->getData('pv') != '') {
                    $pv_per_item = $product->getData('pv');
                }
                $pv_calculated = $item->getQtyOrdered() * $pv_per_item;
                $total_pv += $pv_calculated;

                $items[] = array('sku' => $item->getSku(), 'name' => $item->getName(), 'qty_ordered' => $item->getQtyOrdered(), 'price' => $item->getPrice(), 'pv' => $pv_calculated);
            }
            $data['items'] = $items;

            $data['subtotal'] = $order->getData('subtotal');
            $data['shipping_amount'] = $order->getData('shipping_amount');
            $data['tax_amount'] = $order->getData('tax_amount');
            $data['total_due'] = $order->getData('total_due');
            $data['total_pv'] = (int) $total_pv;

            $orderCollectionLoaded[] = $data;
        }

        return $orderCollectionLoaded;
    }

    /**
     * Get Customer by Customer ID
     *
     * @param int $customerId
     * @return customerRepositoryInterface
     */
    public function getCustomer($customerId)
    {
        $customer = $this->_customerRepositoryInterface->getById($customerId);
        return $customer;
    }

    /**
     * Get Product By SKU
     *
     * @param string $sku
     * @return productRepository
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

}

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

namespace Qsciences\GraphQl\Model\Resolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Qsciences\GraphQl\Helper\Data;
use Qsciences\GraphQl\Model\Resolver\DataProvider\OrderCollection as OrderCollectionProvider;

/**
 * GetDirectScaleOrder field resolver, used for GraphQL request processing
 */
class GetDirectScaleOrder implements ResolverInterface
{
    /**
     * Data Constructor
     *
     * @param OrderCollectionProvider $orderCollectionProvider
     * @param ResourceConnection $resource
     * @param Data $moduleHelper
     */
    public function __construct(
        OrderCollectionProvider $orderCollectionProvider,
        ResourceConnection $resource,
        Data $moduleHelper
    ) {
        $this->orderCollectionProvider = $orderCollectionProvider;
        $this->_resource = $resource;
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
    ) {
        //$accessToken = $this->_moduleHelper->getAccessToken();
        $orderStatus = $this->getOrderStatus($args);
        $createdStartDate = $this->getCreatedStartDate($args);
        $createdEndDate = $this->getCreatedEndDate($args);
        $orderData = $this->getOrderCollection($orderStatus, $createdStartDate, $createdEndDate);

        return $orderData;
    }

    /**
     * @param array $args
     * @return string
     * @throws GraphQlInputException
     */
    private function getOrderStatus(array $args): string
    {
        if (!isset($args['orderStatus'])) {
            throw new GraphQlInputException(__('Order Status should be specified'));
        } elseif ($args['orderStatus'] == "") {
            throw new GraphQlInputException(__('Order Status can not be blank'));
        }

        return $args['orderStatus'];
    }

    /**
     * Get Created Start Date
     *
     * @param array $args
     * @return string
     */
    private function getCreatedStartDate(array $args): string
    {
        if (!isset($args['createdStartDate'])) {
            $args['createdStartDate'] = '';
        }

        return $args['createdStartDate'];
    }

    /**
     * Get Created End Date
     *
     * @param array $args
     * @return string
     */
    private function getCreatedEndDate(array $args): string
    {
        if (!isset($args['createdEndDate'])) {
            $args['createdEndDate'] = '';
        }

        return $args['createdEndDate'];
    }

    /**
     * Get Order Collection
     * 
     * @param string $orderStatus
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getOrderCollection($orderStatus, $createdStartDate, $createdEndDate): array
    {
        $orderCollection = [];
        try {
            $collection = $this->orderCollectionProvider->getData($orderStatus, $createdStartDate, $createdEndDate);
            $orderCollection['orders'] = $collection;
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $orderCollection;
    }

}

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
use Qsciences\GraphQl\Model\Resolver\DataProvider\OrderData as OrderDataProvider;

/**
 * GetOrderEntityId field resolver, used for GraphQL request processing
 */
class GetOrderEntityId implements ResolverInterface
{
    /**
     * Data Constructor
     *
     * @param OrderDataProvider $orderDataProvider
     * @param ResourceConnection $resource
     * @param Data $moduleHelper
     */
    public function __construct(
        OrderDataProvider $orderDataProvider,
        ResourceConnection $resource,
        Data $moduleHelper
    ) {
        $this->orderDataProvider = $orderDataProvider;
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
        $orderNumber = $this->getOrderNumber($args);
        $orderData = $this->getOrderData($orderNumber);

        return $orderData;
    }

    /**
     * @param array $args
     * @return string
     * @throws GraphQlInputException
     */
    private function getOrderNumber(array $args): string
    {
        if (!isset($args['orderNumber'])) {
            throw new GraphQlInputException(__('Order Number should be specified'));
        }

        return $args['orderNumber'];
    }

    /**
     * @param string $orderNumber
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getOrderData($orderNumber): array
    {
        try {
            $orderData = $this->orderDataProvider->getData($orderNumber);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $orderData;
    }

}

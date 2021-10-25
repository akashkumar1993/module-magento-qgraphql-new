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

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * OrderData data provider
 */
class OrderData
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
     * Data Constructor
     *
     * @param OrderInterface $order
     * @param Attribute $entityAttribute
     * @param Config $eavConfig
     * @param FilterEmulate $widgetFilter
     */
    public function __construct(
        OrderInterface $order,
        Attribute $entityAttribute,
        Config $eavConfig,
        FilterEmulate $widgetFilter
    ) {
        $this->order = $order;
        $this->_entityAttribute = $entityAttribute;
        $this->_eavConfig = $eavConfig;
        $this->widgetFilter = $widgetFilter;
    }

    /**
     * @param string $orderNumber
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData($orderNumber): array
    {
        $orderData = $this->order->loadByIncrementId($orderNumber);

        if (false === $orderData->getId()) {
            throw new NoSuchEntityException();
        }

        $orderLoadedData = $orderData->getData();

        return $orderLoadedData;
    }

}

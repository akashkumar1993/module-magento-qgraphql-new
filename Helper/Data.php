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

namespace Qsciences\GraphQl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfig
     */
    protected $_scopeConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * Data Constructor
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
    }

    /**
     * Get Module Config
     *
     * @param string $path
     * @param int $storeId
     * @return string
     */
    public function getModuleConfig($path, $storeId = null): string
    {
        return $this->_scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Function to check if module is enabled
     *
     * @return boolean
     */
    public function chkIsModuleEnable(): bool
    {
        return $this->getModuleConfig(self::Qsciences_GraphQl_XML_PATH_EXTENSIONS . 'isenabled');
    }

    /**
     * Get Table Prefix
     *
     * @return string
     */
    public function getTablePrefix(): string
    {
        $deploymentConfig = $this->_objectManager->get('Magento\Framework\App\DeploymentConfig');
        return $deploymentConfig->get('db/table_prefix');
    }

    /**
     * Function to get access token
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        $headers = getallheaders();
        $headers = array_change_key_case($headers, CASE_LOWER);

        $accessToken = '';
        if (array_key_exists("authorization", $headers)) {
            $accessToken = trim($headers['authorization']);

            if ($accessToken != '') {
                $accessToken = trim($accessToken, 'Bearer ');
            }
        }

        if ($accessToken == '') {
            throw new GraphQlInputException(__('Access Token should be specified.'));
        } else if ($accessToken != 'pj9vmd4x6gy0wvnj4jbaop8ajnzm5nby') {
            throw new GraphQlInputException(__('Access Token is incorrect.'));
        }

        return $accessToken;
    }

}

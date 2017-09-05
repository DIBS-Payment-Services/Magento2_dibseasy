<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Dibs\EasyCheckout\Model\Api;
use Magento\Framework\DataObject;

/**
 * Class Response
 * @package Dibs\EasyCheckout\Model\Api
 */
class Response {

    protected $code;

    protected $response;

    protected $success = false;

    /**
     * DibsEasyPayment_Api_Response constructor.
     *
     * @param $code
     * @param string $responseJson
     */
    public function __construct($code, $responseJson ='')
    {
        $this->code = $code;
        $this->response = $responseJson;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $response
     *
     * @return $this
     */
    public function setResponse($response = '')
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $success
     *
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        $result = '';
        $responseArray = $this->getResponseArray();
        if (isset($responseArray['message'])){
            $result = $responseArray['message'];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $result = [];
        $responseArray = $this->getResponseArray();
        if (isset($responseArray['errors'])){
            $result = $responseArray['errors'];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        $result = [];
        $errors = $this->getErrors();
        foreach ($errors as $errorType){
            foreach ($errorType as $error){
                $result[] = $error;
            }
        }


        return $result;
    }

    /**
     * @return mixed
     */
    public function getResponseArray()
    {
        $result = json_decode($this->response, true);
        return $result;
    }

    /**
     * @return DataObject
     */
    public function getResponseDataObject()
    {
        $dataObject = new DataObject($this->getResponseArray());
        return $dataObject;
    }




}
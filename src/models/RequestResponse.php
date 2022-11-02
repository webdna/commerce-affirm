<?php
/**
 * Commerce Affirm plugin for Craft CMS 4.x
 *
 * @link		https://webdna.co.uk
 * @copyright	Copyright (c) 2022 WebDNA
 */

namespace webdna\commerce\affirm\models;

use Craft;
use craft\commerce\omnipay\base\RequestResponse as BaseRequestResponse;

class RequestResponse extends BaseRequestResponse
{
	
	/**
	 * Check if the transaction was successful.
	 *
	 * @return bool
	 */
	public function isSuccessful(): bool
	{
		$data = $this->getData();
		
		if (isset($data['status'])) {
			return in_array($data['status'], ['authorized', 'captured']);
		}
		
		if (isset($data['type']) && $data['type'] == 'capture') {
			return true;
		}
		
		return false;
	}
	
	
	public function isProcessing(): bool
	{
		return $this->getData()['status'] == '';
	}
	
	/**
	 * Check if the transaction needs to redirect to another page
	 *
	 * @return bool
	 */
	public function isRedirect(): bool
	{
		return $this->getRedirectUrl() !== NULL;
	}
	
	
	/**
	 * Get charge_id param from the response
	 *
	 * @return mixed
	 */
	public function getTransactionReference(): string
	{
		return $this->getData()['id'];
	}
	
	/**
	 * Get amount param from the response
	 *
	 * @return mixed
	 */
	public function getAmount(): float
	{
		return $this->getData()['amount'];
	}
	
	
	/**
	 * Get message param from the response
	 *
	 * @return mixed
	 */
	public function getMessage(): string
	{
		return '';
	}
}
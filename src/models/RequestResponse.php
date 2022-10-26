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
use Omnipay\Affirm\Message\Response\CompletePurchaseResponse;

class RequestResponse extends BaseRequestResponse
{
	public function getMessage(): string
	{
		$data = $this->response->getData();

		if (is_array($data) && !empty($data['status'])) {
			switch ($data['status']) {
				case 'canceled':
					return Craft::t('commerce-affirm', 'The payment was canceled.');
				case 'failed':
					return Craft::t('commerce-affirm', 'The payment failed.');
				case 'expired':
					return Craft::t('commerce-affirm', 'The payment expired.');
			}
		}

		return (string)$this->response->getMessage();
	}

	public function isProcessing(): bool
	{
		$data = $this->response->getData();
		// @TODO Temporary solution ahead of either a PR to `omnipay-mollie` or a gateway rewrite
		if ($this->response instanceof CompletePurchaseResponse && isset($data['method'], $data['status']) && $data['method'] === 'banktransfer' && $this->response->isOpen()) {
			return true;
		}

		return parent::isProcessing();
	}

	public function isRedirect(): bool
	{
		$data = $this->response->getData();
		// @TODO Temporary solution ahead of either a PR to `omnipay-mollie` or a gateway rewrite
		if ($this->response instanceof CompletePurchaseResponse && isset($data['method']) && $data['method'] === 'banktransfer' && $this->isProcessing()) {
			return false;
		}

		return parent::isRedirect();
	}
}
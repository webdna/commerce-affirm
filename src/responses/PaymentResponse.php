<?php

namespace webdna\commerce\affirm\responses;

use Craft;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\errors\NotImplementedException;

class PaymentResponse implements RequestResponseInterface
{
    
    protected object $response;


    public function __construct(object $response)
    {
        $this->response = $response;
    }

    /**
     * @inheritdoc
     */
    public function isSuccessful(): bool
    {
        return (bool)$this->response->isSuccessful();
    }

    /**
     * @inheritdoc
     */
    public function isProcessing(): bool
    {
        return (bool)$this->response->isPending();
    }

    /**
     * @inheritdoc
     */
    public function isRedirect(): bool
    {
        return (bool)$this->response->isRedirect();
    }

    /**
     * @inheritdoc
     */
    public function getRedirectMethod(): string
    {
        return (string)$this->response->getRedirectMethod();
    }

    /**
     * @inheritdoc
     */
    public function getRedirectData(): array
    {
        return $this->response->getRedirectData() ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrl(): string
    {
        return (string)$this->response->getRedirectUrl();
    }

    /**
     * @inheritdoc
     */
    public function getTransactionReference(): string
    {
        $data = $this->getData();
		
		if (isset($data['status']) && in_array($data['status'], ['authorized', 'captured'])) {
            return (string)$this->response->getTransactionReference();
		}
		
		if (isset($data['type']) && $data['type'] == 'capture') {
			return (string)$data['id'];
		}

        return (string)$this->response->getTransactionReference();
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return (string)$this->response->getCode();
    }

    /**
     * @inheritdoc
     */
    public function getMessage(): string
    {
        return $this->response->isSuccessful() ? '' : (string)$this->response->getMessage();
    }

    /**
     * @inheritdoc
     */
    public function redirect(): void
    {
        $this->response->redirect();
    }

    /**
     * @inheritdoc
     */
    public function getData(): mixed
    {
        return $this->response->getData();
    }
}

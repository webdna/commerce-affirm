<?php
/**
 * Commerce Affirm plugin for Craft CMS 4.x
 *
 * @link		https://webdna.co.uk
 * @copyright	Copyright (c) 2022 WebDNA
 */

namespace webdna\commerce\affirm\gateways;

use webdna\commerce\affirm\Affirm;
use webdna\commerce\affirm\models\forms\AffirmOffsitePaymentForm;
use webdna\commerce\affirm\models\RequestResponse;
use webdna\commerce\affirm\responses\PaymentResponse;

use Craft;
use craft\commerce\Plugin as Commerce;
// use craft\commerce\base\Gateway as BaseGateway;
use craft\commerce\errors\PaymentException;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\errors\CurrencyException;
use craft\commerce\errors\OrderStatusException;
use craft\commerce\errors\TransactionException;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\Transaction;
use craft\commerce\records\Transaction as TransactionRecord;
use craft\commerce\omnipay\base\Gateway as BaseGateway;
use craft\commerce\omnipay\base\OffsiteGateway;
use craft\errors\ElementNotFoundException;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\web\Response;
use craft\web\View;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\CreditCard;
use Omnipay\Common\ItemBag;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Issuer;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Common\PaymentMethod;
use Omnipay\Omnipay;
use Omnipay\Affirm\Gateway as OmnipayGateway;
use Omnipay\Affirm\Message\Request\FetchTransactionRequest;
use Omnipay\Affirm\Message\Response\FetchPaymentMethodsResponse;

/**
 * @author		WebDNA
 * @package		CommerceAffirm
 * @since		1.0.0
 */

class Gateway extends BaseGateway
{
	private ?string $_publicKey = null;
	
	private ?string $_privateKey = null;
	
	private ?string $_productKey = null;
	
	private bool|string $_testMode = false;
	
	
	
	public static function displayName(): string
	{
		return Craft::t('commerce', 'Affirm');
	}
	
	public function init(): void
	{
		parent::init();
	}
	
	
	public function getSettings(): array
	{
		$settings = parent::getSettings();
		$settings['publicKey'] = $this->getPublicKey(true);
		$settings['privateKey'] = $this->getPrivateKey(true);
		$settings['productKey'] = $this->getProductKey(true);
		$settings['testMode'] = $this->getTestMode(false);
	
		return $settings;
	}
	
	public function getPublicKey(bool $parse = true): ?string
	{
		return $parse ? App::parseEnv($this->_publicKey) : $this->_publicKey;
	}
	
	public function setPublicKey(?string $publicKey): void
	{
		$this->_publicKey = $publicKey;
	}
	
	public function getPrivateKey(bool $parse = true): ?string
	{
		return $parse ? App::parseEnv($this->_privateKey) : $this->_privateKey;
	}
	
	public function setPrivateKey(?string $privateKey): void
	{
		$this->_privateKey = $privateKey;
	}
	
	public function getProductKey(bool $parse = true): ?string
	{
		return $parse ? App::parseEnv($this->_productKey) : $this->_productKey;
	}
	
	public function setProductKey(?string $productKey): void
	{
		$this->_productKey = $productKey;
	}
	
	public function getTestMode(bool $parse = true): bool|string
	{
		return $parse ? App::parseBooleanEnv($this->_testMode) : $this->_testMode;
	}
	
	public function setTestMode(bool|string $testMode): void
	{
		$this->_testMode = $testMode;
	}
	
	
	public function populateRequest(array &$request, BasePaymentForm $paymentForm = null): void
	{
		if ($paymentForm) {
			
			if ($paymentForm->token) {
				$request['transaction_id'] = $paymentForm->token;
			}
		}
	}
	
	
	public function createPaymentRequest(Transaction $transaction, ?CreditCard $card = null, ?ItemBag $itemBag = null): array
	{
		$request = [
			"order_id" => $transaction->order->number,
		];
		
		return $request;
	}
	
	public function supportsCapture(): bool
	{
		return false;
	}
	
	public function supportsRefund(): bool
	{
		return false;
	}

	public function supportsCompletePurchase(): bool
	{
		return false;
	}

	public function supportsPurchase(): bool
	{
		return true;
	}

	public function supportsAuthorize(): bool
	{
		return false;
	}

	public function completePurchase(Transaction $transaction): RequestResponseInterface
	{
		if (!$this->supportsCompletePurchase()) {
	 		throw new NotSupportedException(Craft::t('commerce', 'Completing purchase is not supported by this gateway'));
		}
	}
	
	public function getSettingsHtml(): ?string
	{
		return Craft::$app->getView()->renderTemplate('commerce-affirm/gatewaySettings', ['gateway' => $this]);
	}
	
	public function getPaymentFormModel(): BasePaymentForm
	{
		return new AffirmOffsitePaymentForm();
	}
	
	public function getPaymentFormHtml(array $params): ?string
	{
		try {
			$defaults = [
				'gateway' => $this,
				'paymentForm' => $this->getPaymentFormModel(),
			];
		} catch (\Throwable $exception) {
			// In case this is not allowed for the account
			return parent::getPaymentFormHtml($params);
		}
	
		$params = array_merge($defaults, $params);
	
		$view = Craft::$app->getView();
	
		$previousMode = $view->getTemplateMode();
		$view->setTemplateMode(View::TEMPLATE_MODE_CP);
			
		//$view->registerJsFile('https://cdn.worldpay.com/v1/worldpay.js');
		//$view->registerAssetBundle(WorldpayPaymentBundle::class);
	
		$html = $view->renderTemplate('commerce-affirm/paymentForm', $params);
	
		$view->setTemplateMode($previousMode);
	
		return $html;
	}

	public function purchase(Transaction $transaction, BasePaymentForm $form): RequestResponseInterface
	{
		try {
			$gateway = $this->createGateway();
			$response = $gateway->authorize([
				'transaction_id' => $form->token,
			])->send();

			$authorizeResponse = new PaymentResponse($response);

			if($authorizeResponse->isSuccessful()){
				$responseCapture = $gateway->capture([
					'transactionReference' => $authorizeResponse->getTransactionReference(),
				])->send();

				return new PaymentResponse($responseCapture);
			}
			return new PaymentResponse($response);

		} catch (\Exception $exception) {
			$message = $exception->getMessage();
			Craft::dd($message);
			if ($message) {
				throw new PaymentException($message);
			}
			throw new PaymentException('The payment could not be processed (' . get_class($exception) . ')');
		}
	}
	
	public function rules(): array
	{
		$rules = parent::rules();
	
		return $rules;
	}
	
	protected function createGateway(): AbstractGateway
	{
		/** @var OmnipayGateway $gateway */
		$gateway = Omnipay::create('Affirm');
	
		$gateway->setPublicKey($this->getPublicKey());
		$gateway->setPrivateKey($this->getPrivateKey());
		$gateway->setProductKey($this->getProductKey());
		$gateway->setTestMode($this->getTestMode());
		
		return $gateway;
	}
	
	protected function getGatewayClassName(): ?string
	{
		return '\\' . OmnipayGateway::class;
	}
	
	
}
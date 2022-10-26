<?php
/**
 * Commerce Affirm plugin for Craft CMS 4.x
 *
 * @link		https://webdna.co.uk
 * @copyright	Copyright (c) 2022 WebDNA
 */

namespace webdna\commerce\affirm;

use webdna\commerce\affirm\services\AffirmService;
use webdna\commerce\affirm\gateways\Gateway;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\commerce\services\Gateways;
use craft\events\RegisterComponentTypesEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\log\Logger;
use craft\log\MonologTarget;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use yii\base\Event;

/**
 * Class Affirm
 *
 * @author		WebDNA
 * @package		CommerceAffirm
 * @since		1.0.0
 */

class Affirm extends Plugin
{
	// Static Properties
	// =========================================================================
	
	public static $plugin;
	
	// Public Properties
	// =========================================================================
	
	public string $schemaVersion = '1.0.0';
	
	public bool $hasCpSettings = false;
	
	public bool $hasCpSection = false;
	
	// Public Methods
	// =========================================================================
	
	public function init(): void
	{
		parent::init();
		self::$plugin = $this;
			
		Event::on(
			Gateways::class,
			Gateways::EVENT_REGISTER_GATEWAY_TYPES,
			function (RegisterComponentTypesEvent $event) {
				$event->types[] = Gateway::class;
			}
		);
		
		
		Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
			'name' => 'affirm',
			'categories' => ['affirm'],
			'level' => Logger::LEVEL_INFO,
			'logContext' => false,
			'allowLineBreaks' => false,
			'formatter' => new LineFormatter(
				format: "%datetime% %message%\n",
				dateFormat: 'Y-m-d H:i:s',
			),
		]);
		
		
		Craft::info(
			Craft::t(
				'commerce-affirm',
				'{name} plugin loaded',
				['name' => $this->name]
			),
			__METHOD__
		);
	}
	
	public static function log(string $message): void
	{
		Craft::getLogger()->log($message, Logger::LEVEL_INFO, 'affirm');
	}
	
	public static function error(string $message): void
	{
		Craft::getLogger()->log($message, Logger::LEVEL_ERROR, 'affirm');
	}
}
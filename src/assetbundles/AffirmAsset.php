<?php
/**
 * Commerce Affirm plugin for Craft CMS 4.x
 *
 * @link		https://webdna.co.uk
 * @copyright	Copyright (c) 2022 WebDNA
 */

namespace webdna\commerce\affirm\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author		WebDNA
 * @package		CommerceAffirm
 * @since		1.0.0
 */

class AffirmAsset extends AssetBundle
{
	public function init(): void
	{
		$this->sourcePath = "@webdna/commerce/affirm/assetbundles/dist";
		
		$this->depends = [
			CpAsset::class,
		];
		
		$this->js = [
			'js/affirm.js',
		];
		
		$this->css = [
			'css/affirm.css',
		];
		
		parent::init();
	}
}
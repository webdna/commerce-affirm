<?php
/**
 * Commerce Affirm plugin for Craft CMS 4.x
 *
 * @link		https://webdna.co.uk
 * @copyright	Copyright (c) 2022 WebDNA
 */

namespace webdna\commerce\affirm\models\forms;

use craft\commerce\models\payments\BasePaymentForm;

class AffirmOffsitePaymentForm extends BasePaymentForm
{
	public ?string $token = null;

}
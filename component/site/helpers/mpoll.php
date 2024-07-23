<?php

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;

class MPollHelper {
	public static function getConfig() {
		$config = ComponentHelper::getParams('com_mpoll');
		$cfg = $config->toObject();
		return $cfg;
	}

}

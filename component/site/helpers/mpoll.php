<?php

defined('_JEXEC') or die('Restricted access');

class MPollHelper {
	public static function getConfig() {
		$config = JComponentHelper::getParams('com_mpoll');
		$cfg = $config->toObject();
		return $cfg;
	}

}

<?php


defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Build the route for the com_mpoll component
 *
 * @param	array	An array of URL arguments
 *
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
function MPollBuildRoute(&$query)
{
	$items = Array();
	$default = 0;
	$founditem = 0;
	$segments = array();
	$app = JFactory::getApplication();
	$menu	= $app->getMenu();
	$items	= $menu->getItems('component', 'com_mpoll');

	if (isset($query['poll'])) $poll = $query['poll']; else $poll =0;

	foreach ($items as $mi) {
		if (!$founditem) {
			if (isset($mi->query['poll'])) {
				if ( ! empty( $mi->query['poll'] ) && ( (int) $mi->query['poll'] == (int) $poll ) ) {
					if ( ! empty( $mi->query['task'] ) && ( $mi->query['task'] == 'results' ) && $query['task'] == 'results' ) {
						$founditem = $mi->id;
					} else if ( ! empty( $mi->query['task'] ) && ( $mi->query['task'] == 'ballot' ) && $query['task'] == 'ballot' ) {
						$founditem = $mi->id;
					}
				}
			}
		}
	}

	
	
	if (!$founditem) {
		$default = $query['Itemid'];
	}
		

	if ($founditem) {
		$query['Itemid'] = $founditem;
		unset ($query['view']);
		unset ($query['task']);
		unset ($query['poll']);
	} else {
		$query['Itemid'] = $default;
	}

	return $segments;
}
/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 */
function MPollParseRoute($segments)
{
	$vars = array();
	return $vars;
}
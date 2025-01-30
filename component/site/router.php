<?php
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Router\Router;


defined('_JEXEC') or die;

class MPollRouter extends RouterView
{
	public function __construct($app = null, $menu = null)
	{
		$params = JComponentHelper::getParams('com_mpoll');

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));

		JLoader::register('MPollRouterRulesLegacy', __DIR__ . '/helpers/legacyrouter.php');
		$this->attachRule(new MPollRouterRulesLegacy($this));

        // needed for Joomla 4
        $router = $app::getRouter();
        $router->attachParseRule([$this, 'parseProcessAfter'], Router::PROCESS_AFTER);

    }

    /**
     * @param Router $router
     * @param Uri    $uri
     *
     * @return void
     */
    public function parseProcessAfter(Router $router, Uri $uri)
    {
        // Kinda crazy but needed in Joomla 4
        $uri->setPath(null);
    }
}

/**
 * Build the route for the com_mpoll component
 *
 * @param	array	An array of URL arguments
 *
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
function MPollBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new MPollRouter($app, $app->getMenu());

	return $router->build($query);
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
	$app = JFactory::getApplication();
	$router = new MPollRouter($app, $app->getMenu());

	return $router->parse($segments);
}
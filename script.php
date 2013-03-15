<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
class com_mpollInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
		// Create categories for our component
		$basePath = JPATH_ADMINISTRATOR . '/components/com_categories';
		require_once $basePath . '/models/category.php';
		$config = array( 'table_path' => $basePath . '/tables');
		$catmodel = new CategoriesModelCategory( $config);
		$catData = array( 'id' => 0, 'parent_id' => 0, 'level' => 1, 'path' => 'testentry', 'extension' => 'com_mpoll'
				, 'title' => 'Uncategorised', 'alias' => 'testentry', 'description' => '', 'published' => 1, 'language' => '*');
		$status = $catmodel->save( $catData);
		
		if(!$status)
		{
			JError::raiseWarning(500, JText::_('Unable to create default category!'));
		}
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
	}
}
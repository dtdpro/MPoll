<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Installer\Installer;

class com_mpollInstallerScript {

    // interface

    public function preflight($route, $adapter) {

        return true;
    }

    public function postflight($route, $adapter) {

        if ($route == 'install' || $route == 'update') {
            // set "add" parameter in forms menu item in administration
            $db = Factory::getDbo();
            $where = $db->quoteName('menutype') . ' = ' . $db->quote('main') . ' AND ' . $db->quoteName('client_id') . ' = 1 AND ' . $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_mpoll&view=mpolls');
            $this->setParams(array("menu-quicktask" => "index.php?option=com_mpoll&task=mpoll.add"), 'menu', 'params', $where);
        }

        return true;
    }

    public function uninstall( $adapter) {

    }

    private function setParams($param_array, $table, $fieldName, $where = "") {
        if (count($param_array) > 0) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(array('id', $fieldName)))->from($db->quoteName('#__' . $table));
            if ($where != "") {
                $query->where($where);
            }
            $results = new stdClass();
            try {
                $db->setQuery($query);
                $results = $db->loadObjectList();
            }
            catch (RuntimeException $e) {
            }
            if ($results) {
                foreach ($results as $result) {
                    $params = json_decode($result->$fieldName, true);
                    // add the new variable(s) to the existing one(s)
                    foreach ($param_array as $name => $value) {
                        $params[(string)$name] = (string)$value;
                    }
                    // store the combined new and existing values back as a JSON string
                    $paramsString = json_encode($params);
                    try {
                        $db->setQuery('UPDATE #__' . $table . ' SET ' . $fieldName . ' = ' .
                            $db->quote($paramsString) . ' WHERE id=' . $result->id);
                        $db->execute();

                    }
                    catch (RuntimeException $e) {
                    }
                }
            }
        }
    }


}
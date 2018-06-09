<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.overrides
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugin class for overrids diff view handling.
 *
 * @since  __VERSION__
 */
class PlgSystemOverrides extends CMSPlugin
{

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe -- event dispatcher.
	 *
	 * @since   __VERSION__
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// If we are not on admin don't process.
		if ($this->app->isClient('site'))
		{
			return;
		}
	}

	/**
	 * Methode to collect data for diff view of overrides. (called in /administrator/components/com_installer/Model/InstallModel.php)
	 *
	 * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   __VERSION__
		 */
	public function onInstallerSaveOverrides($context)
	{
		return true;
	}
	/**
	 * Methode to handle diff view of overrides. (called in /administrator/components/com_installer/Model/InstallModel.php)
	 *
	 * @param   string   $context    The context of the content passed to the plugin (added in 1.6)
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   __VERSION__
	 */
	public function onInstallerCheckOverrides($context)
	{
		return true;
	}
}

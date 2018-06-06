<?php
/**
 * @package     Agosm
 *
 * @copyright   Copyright (C) 2018 Astrid Günther. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('AgosmsHelper', JPATH_ADMINISTRATOR . '/components/com_agosms/helpers/agosms.php');
JLoader::register('AgosmsHelperRoute', JPATH_SITE . '/components/com_agosms/helpers/route.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Agosms Component Association Helper
 *
 * @since  3.0
 */
abstract class AgosmsHelperAssociation extends CategoryHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since   3.0
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		$jinput = JFactory::getApplication()->input;
		$view   = is_null($view) ? $jinput->get('view') : $view;
		$id     = empty($id) ? $jinput->getInt('id') : $id;

		if ($view === 'agosm')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_agosms', '#__agosms', 'com_agosms.item', $id);

				$return = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = AgosmsHelperRoute::getAgosmRoute($item->id, (int) $item->catid, $item->language);
				}

				return $return;
			}
		}

		if ($view == 'category' || $view == 'categories')
		{
			return self::getCategoryAssociations($id, 'com_agosms');
		}

		return array();
	}
}

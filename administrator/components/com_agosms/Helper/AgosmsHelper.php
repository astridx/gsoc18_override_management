<?php
/**
 * @package     Agosm
 *
 * @copyright   Copyright (C) 2018 Astrid Günther. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Agosms\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Table\Table;

/**
 * Agosms helper.
 *
 * @since  1.6
 */
class AgosmsHelper extends ContentHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName = 'agosms')
	{
		\JHtmlSidebar::addEntry(
			\JText::_('COM_AGOSMS_SUBMENU_AGOSMS'),
			'index.php?option=com_agosms&view=agosms',
			$vName == 'agosms'
		);

		\JHtmlSidebar::addEntry(
			\JText::_('COM_AGOSMS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_agosms',
			$vName == 'categories'
		);

		if (ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_contact')->get('custom_fields_enable', '1'))
		{
			\JHtmlSidebar::addEntry(
				\JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_agosms.agosm',
				$vName == 'fields.fields'
			);

			\JHtmlSidebar::addEntry(
				\JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_agosms.agosm',
				$vName == 'fields.groups'
			);
		}
	}

	/**
	 * Update / reset the banners
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public static function updateReset()
	{
/*		$db       = \JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$query    = $db->getQuery(true)
			->select('*')
			->from('#__banners')
			->where($db->quote(\JFactory::getDate()) . ' >= ' . $db->quote('reset'))
			->where($db->quoteName('reset') . ' != ' . $db->quote($nullDate) . ' AND ' . $db->quoteName('reset') . '!= NULL')
			->where(
				'(' . $db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' = '
				. (int) $db->quote(\JFactory::getUser()->id) . ')'
			);
		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		foreach ($rows as $row)
		{
			$purchaseType = $row->purchase_type;

			if ($purchaseType < 0 && $row->cid)
			{
				$client = Table::getInstance('Client', '\\Joomla\\Component\\Banners\\Administrator\\Table\\');
				$client->load($row->cid);
				$purchaseType = $client->purchase_type;
			}

			if ($purchaseType < 0)
			{
				$params = ComponentHelper::getParams('com_banners');
				$purchaseType = $params->get('purchase_type');
			}

			switch ($purchaseType)
			{
				case 1:
					$reset = $nullDate;
					break;
				case 2:
					$date = \JFactory::getDate('+1 year ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 3:
					$date = \JFactory::getDate('+1 month ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 4:
					$date = \JFactory::getDate('+7 day ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
				case 5:
					$date = \JFactory::getDate('+1 day ' . date('Y-m-d'));
					$reset = $db->quote($date->toSql());
					break;
			}

			// Update the row ordering field.
			$query->clear()
				->update($db->quoteName('#__banners'))
				->set($db->quoteName('reset') . ' = ' . $db->quote($reset))
				->set($db->quoteName('impmade') . ' = ' . $db->quote(0))
				->set($db->quoteName('clicks') . ' = ' . $db->quote(0))
				->where($db->quoteName('id') . ' = ' . $db->quote($row->id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
			{
				\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
*/
		return true;
	}


	/**
	 * Adds Count Items for WebLinks Category Manager.
	 *
	 * @param   stdClass[]  &$items  The agosms category objects.
	 *
	 * @return  stdClass[]  The agosms category objects.
	 *
	 * @since   3.6.0
	 */
	public static function countItems(&$items)
	{
		$db = \JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_archived    = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;

			$query = $db->getQuery(true)
				->select('state, COUNT(*) AS count')
				->from($db->qn('#__agosms'))
				->where($db->qn('catid') . ' = ' . (int) $item->id)
				->group('state');

			$db->setQuery($query);
			$agosms = $db->loadObjectList();

			foreach ($agosms as $agosm)
			{
				if ($agosm->state == 1)
				{
					$item->count_published = $agosm->count;
				}
				elseif ($agosm->state == 0)
				{
					$item->count_unpublished = $agosm->count;
				}
				elseif ($agosm->state == 2)
				{
					$item->count_archived = $agosm->count;
				}
				elseif ($agosm->state == -2)
				{
					$item->count_trashed = $agosm->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The agosm tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.7.0
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;

			$query = $db->getQuery(true);
			$query->select('published as state, count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id')
				->group('state');

			$db->setQuery($query);
			$agosms = $db->loadObjectList();

			foreach ($agosms as $agosm)
			{
				if ($agosm->state == 1)
				{
					$item->count_published = $agosm->count;
				}
				if ($agosm->state == 0)
				{
					$item->count_unpublished = $agosm->count;
				}
				if ($agosm->state == 2)
				{
					$item->count_archived = $agosm->count;
				}
				if ($agosm->state == -2)
				{
					$item->count_trashed = $agosm->count;
				}
			}
		}

		return $items;
	}
}

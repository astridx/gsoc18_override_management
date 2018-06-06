<?php
/**
 * @package     Agosm
 *
 * @copyright   Copyright (C) 2018 Astrid Günther. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Agosms\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;


/**
 * Methods supporting a list of agosm records.
 *
 * @since  1.6
 */
class AgosmsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_id',
				'c.title', 'category_title',
				'state', 'a.state', 'published',
				'access', 'a.access',
				'ag.title', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'l.title', 'language_title',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'url', 'a.url',
				'tag',
				'level', 'c.level',
			);

			$assoc = \JLanguageAssociations::isEnabled();

			if ($assoc)
			{
				$config['filter_fields'][] = 'association';
			}
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		// Load the parameters.
		$this->setState('params', ComponentHelper::getParams('com_agosms'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.tag');
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = \JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid, a.created, a.created_by, ' .
				'a.hits, a.state, a.access, a.ordering, a.language, a.publish_up, a.publish_down'
			)
		);
		$query->from($db->quoteName('#__agosms', 'a'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->select($db->quoteName('l.image', 'language_image'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->qn('l.lang_code') . ' = ' . $db->qn('a.language'));

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'))
			->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->qn('uc.id') . ' = ' . $db->qn('a.checked_out'));

		// Join over the asset groups.
		$query->select($db->quoteName('ag.title', 'access_level'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->qn('ag.id') . ' = ' . $db->qn('a.access'));

		// Join over the categories.
		$query->select('c.title AS category_title')
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.catid'));

		// Join over the associations.
		/*$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 AS association')
				->join('LEFT', $db->quoteName('#__associations', 'asso') . ' ON asso.id = a.id AND asso.context = ' . $db->quote('com_agosms.item'))
				->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON asso2.key = asso.key')
				->group('a.id, l.title, l.image, uc.name, ag.title, c.title');
		}*/

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where($db->quoteName('a.access') . ' = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where($db->quoteName('a.access') . ' IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where($db->quoteName('a.state') . ' = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(' . $db->quoteName('a.state') . ' IN (0, 1))');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where($db->quoteName('c.level') . ' <= ' . (int) $level);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $search . ' OR ' . $db->quoteName('a.alias') . ' LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where($db->quoteName('a.language') . ' = ' . $db->quote($language));
		}

		$tagId = $this->getState('filter.tag');

		// Filter by a single tag.
		if (is_numeric($tagId))
		{
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
				->join(
					'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_agosms.agosm')
				);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol == 'a.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'c.title ' . $orderDirn . ', a.ordering';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Agosm', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

}

<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\View\Banners;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Banners\Administrator\Helper\BannersHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * View class for a list of banners.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Category data
	 *
	 * @var  array
	 */
	protected $categories;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  \JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise an \Exception object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->categories    = $this->get('CategoryOrders');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		BannersHelper::addSubmenu('banners');

		$this->addToolbar();

		// Include the component HTML helpers.
		HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		$this->sidebar = \JHtmlSidebar::render();

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		\JLoader::register('BannersHelper', JPATH_ADMINISTRATOR . '/components/com_banners/helpers/banners.php');

		$canDo = ContentHelper::getActions('com_banners', 'category', $this->state->get('filter.category_id'));
		$user  = Factory::getUser();

		ToolbarHelper::title(Text::_('COM_BANNERS_MANAGER_BANNERS'), 'bookmark banners');

		if (count($user->getAuthorisedCategories('com_banners', 'core.create')) > 0)
		{
			ToolbarHelper::addNew('banner.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			if ($this->state->get('filter.published') != 2)
			{
				ToolbarHelper::publish('banners.publish', 'JTOOLBAR_PUBLISH', true);
				ToolbarHelper::unpublish('banners.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			}

			if ($this->state->get('filter.published') != -1)
			{
				if ($this->state->get('filter.published') != 2)
				{
					ToolbarHelper::archiveList('banners.archive');
				}
				elseif ($this->state->get('filter.published') == 2)
				{
					ToolbarHelper::unarchiveList('banners.publish');
				}
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::checkin('banners.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_banners')
			&& $user->authorise('core.edit', 'com_banners')
			&& $user->authorise('core.edit.state', 'com_banners'))
		{
			$title = Text::_('JTOOLBAR_BATCH');

			// Instantiate a new FileLayout instance and render the batch button
			$layout = new FileLayout('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			Toolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'banners.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('banners.trash');
		}

		if ($user->authorise('core.admin', 'com_banners') || $user->authorise('core.options', 'com_banners'))
		{
			ToolbarHelper::preferences('com_banners');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_BANNERS_BANNERS');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'ordering'    => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'     => Text::_('JSTATUS'),
			'a.name'      => Text::_('COM_BANNERS_HEADING_NAME'),
			'a.sticky'    => Text::_('COM_BANNERS_HEADING_STICKY'),
			'client_name' => Text::_('COM_BANNERS_HEADING_CLIENT'),
			'impmade'     => Text::_('COM_BANNERS_HEADING_IMPRESSIONS'),
			'clicks'      => Text::_('COM_BANNERS_HEADING_CLICKS'),
			'a.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.id'        => Text::_('JGRID_HEADING_ID'),
		);
	}
}

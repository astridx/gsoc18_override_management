<?php
/**
 * @package     Agosm
 *
 * @copyright   Copyright (C) 2018 Astrid Günther. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Agosms\Administrator\View\Agosms;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Agosms\Administrator\Helper\AgosmsHelper;
use Joomla\CMS\Helper\ContentHelper;

/**
 * View class for a list of agosms.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
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
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
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

		// Modal layout doesn't need the submenu.
		if ($this->getLayout() !== 'modal')
		{
			AgosmsHelper::addSubmenu('agosms');
		}


		// We don't need toolbar in the modal layout.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = \JHtmlSidebar::render();
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into an hidden field.
				$languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
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
		$canDo = ContentHelper::getActions('com_contact', 'category', $this->state->get('filter.category_id'));
		$user  = \JFactory::getUser();

		\JToolbarHelper::title(\JText::_('COM_AGOSMS_MANAGER_AGOSMS'), 'link agosms');

		if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_agosms', 'core.create')) > 0)
		{
			\JToolbarHelper::addNew('agosm.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			\JToolbarHelper::publish('contacts.publish', 'JTOOLBAR_PUBLISH', true);
			\JToolbarHelper::unpublish('contacts.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			\JToolbarHelper::custom('contacts.featured', 'featured.png', 'featured_f2.png', 'JFEATURE', true);
			\JToolbarHelper::custom('contacts.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
			\JToolbarHelper::archiveList('contacts.archive');
			\JToolbarHelper::checkin('contacts.checkin');
		}


		\JToolbarHelper::help('JHELP_COMPONENTS_AGOSMS_AGOSMS');

		\JHtmlSidebar::setAction('index.php?option=com_agosms');

/*
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');


		if (count($user->getAuthorisedCategories('com_agosms', 'core.create')) > 0)
		{
			JToolbarHelper::addNew('agosm.add');
		}

		if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('agosm.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('agosms.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('agosms.unpublish', 'JTOOLBAR_UNPUBLISH', true);

			JToolbarHelper::archiveList('agosms.archive');
			JToolbarHelper::checkin('agosms.checkin');
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'agosms.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('agosms.trash');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_agosms') && $user->authorise('core.edit', 'com_agosms')
			&& $user->authorise('core.edit.state', 'com_agosms'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($user->authorise('core.admin', 'com_agosms') || $user->authorise('core.options', 'com_agosms'))
		{
			JToolbarHelper::preferences('com_agosms');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_AGOSMS_LINKS');*/
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
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.title' => JText::_('JGLOBAL_TITLE'),
			'a.access' => JText::_('JGRID_HEADING_ACCESS'),
			'a.hits' => JText::_('JGLOBAL_HITS'),
			'a.language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}

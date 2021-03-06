<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Workflow\WorkflowServiceInterface;

/**
 * Workflow States field.
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowconditionField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Workflowcondition';

	/**
	 * The component and section separated by ".".
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension = '';

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$success = parent::setup($element, $value, $group);

		if ($success)
		{
			if (strlen($element['extension']))
			{
				$this->extension = (string) $element['extension'];
			}
			else
			{
				$this->extension = Factory::getApplication()->input->getCmd('extension');
			}
		}

		return $success;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$fieldname  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
		$options    = [];
		$conditions = [];

		$parts = explode('.', $this->extension);

		$component = Factory::getApplication()->bootComponent($parts[0]);

		if ($component instanceof WorkflowServiceInterface)
		{
			$conditions = $component->getConditions($this->extension);
		}

		foreach ($conditions as $value => $option)
		{
			$text = trim((string) $option) != '' ? trim((string) $option) : $value;

			$selected = ((int) $this->value === $value);

			$tmp = array(
				'value'    => $value,
				'text'     => Text::alt($text, $fieldname),
				'selected' => $selected,
				'checked'  => $selected,
			);

			// Add the option object to the result set.
			$options[] = (object) $tmp;
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}

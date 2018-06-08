<?php
/**
 * @package     Joomla.Admin
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $asset The asset text
 * @var  string   $authorField The label text
 * @var  integer  $authorId The author id
 * @var  string   $class The class text
 * @var  boolean  $disabled True if field is disabled
 * @var  string   $folder The folder text
 * @var  string   $id The label text
 * @var  string   $link The link text
 * @var  string   $name The name text
 * @var  string   $preview The preview image relative path -> default false
 * @var  integer  $previewHeight The image preview height -> default false
 * @var  integer  $previewWidth The image preview width -> default false
 * @var  string   $onchange  The onchange text
 * @var  boolean  $readonly True if field is readonly
 * @var  integer  $size The size text
 * @var  string   $value The value text
 * @var  string   $src The path and filename of the image
 */
extract($displayData);

$attr = '';

// Initialize some field attributes.
$attr .= !empty($class) ? ' class="form-control hasTooltip field-agosm-input ' . $class . '"' : ' class="form-control hasTooltip field-agosm-input"';
$attr .= !empty($size) ? ' size="' . $size . '"' : '';

// Initialize JavaScript field attributes.
$attr .= !empty($onchange) ? ' onchange="' . $onchange . '"' : '';

$showPreview = false;

// The url for the modal
$url = 'index.php?option=com_agosms&amp;view=button&amp;tmpl=component';
?>
<joomla-field-agosm class="field-agosm-wrapper"
		type="image" <?php // @TODO add this attribute to the field in order to use it for all agosm types ?>
		base-path="<?php echo Uri::root(); ?>"
		root-folder="<?php echo ComponentHelper::getParams('com_agosm')->get('file_path', 'images'); ?>"
		url="<?php echo $url; ?>"
		modal-container=".modal"
		modal-width="100%"
		modal-height="400px"
		input=".field-agosm-input"
		button-select=".button-select"
		button-clear=".button-clear"
		button-save-selected=".button-save-selected"
>
	<?php
	// Render the modal
	echo HTMLHelper::_('bootstrap.renderModal',
		'agosmModal_'. $id,
		array(
			'url'         => $url,
			'title'       => Text::_('MOD_AGOSM_CHANGE_CORDS'),
			'closeButton' => true,
			'height' => '100%',
			'width'  => '100%',
			'modalWidth'  => '80',
			'bodyHeight'  => '60',
			'footer'      => '<button class="btn btn-secondary button-agosm-save-selected">' . Text::_('JSELECT') . '</button><button class="btn btn-secondary" data-dismiss="modal">' . Text::_('JCANCEL') . '</button>'
		)
	);

	HTMLHelper::_('script', 'com_agosms/joomla-field-agosm.js', ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => true]);

	?>
	<div class="input-group">
		<input type="text" role="input" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" <?php echo $attr; ?>>
		<?php if ($disabled != true) : ?>
			<div class="input-group-append">
				<a class="btn btn-secondary button-select-agosm"><?php echo Text::_("JLIB_FORM_BUTTON_SELECT"); ?></a>
			</div>
		<?php endif; ?>
	</div>
</joomla-field-agosm>

<?php
/**
 * @package     Agosm
 *
 * @copyright   Copyright (C) 2018 Astrid Günther. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$this->subtemplatename = 'items';
echo JLayoutHelper::render('joomla.content.category_default', $this);

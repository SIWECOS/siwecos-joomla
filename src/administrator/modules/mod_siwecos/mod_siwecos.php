<?php
/**
 * @version    %%MODULEVERSION%%
 * @package    ModSiwecos
 * @copyright  Copyright (C) 2017 CMS-Garden e.V.
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.siwecos.de
 */

defined('_JEXEC') or die;

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

$plugin = JPluginHelper::getPlugin('system', 'siwecos');

// Check if plugin is installed and published
if (!is_object($plugin))
{
  return; 
}

$pluginParams = new JRegistry($plugin->params);

require JModuleHelper::getLayoutPath('mod_siwecos', $params->get('layout', 'default'));

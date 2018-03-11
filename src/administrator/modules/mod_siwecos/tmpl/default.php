<?php
/**
 * @version    %%MODULEVERSION%%
 * @package    ModSiwecos
 * @copyright  Copyright (C) 2017 CMS-Garden e.V.
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.siwecos.de
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'mod_siwecos/mod_siwecos.js', array('relative' => true, 'version'=>'auto'));
JHtml::_('script', 'mod_siwecos/jquery.AshAlom.gaugeMeter-2.0.0.min.js', array('relative' => true));
JHtml::_('stylesheet', 'mod_siwecos/mod_siwecos.css', array('relative' => true));

JText::script('MOD_SIWECOS_RESULTS_DOMAIN_NOT_FOUND');
JText::script('MOD_SIWECOS_SCAN_STARTED');

?>
<div class="mod_siwecos <?php echo $moduleclass_sfx ?>">
    <?php if(!$pluginParams->get('authToken')): ?>
        <div class="mod_siwecos_login">
            <?php echo JText::_('MOD_SIWECOS_PLEASE_LOGIN'); ?><br/>
            <a href="<?php echo JRoute::_('index.php?option=com_plugins&view=plugin&task=plugin.edit&layout=edit&extension_id=' . $plugin->id); ?>" class="btn-primary btn"><?php echo JText::_('MOD_SIWECOS_BUTTON_CONFIGURATION_LABEL'); ?></a>
        </div>
    <?php else: ?>
        <div class="mod_siwecos_resultbox">
            <div id="mod_siwecos_loadingtext">
                <?php echo JText::_('MOD_SIWECOS_RESULTS_FETCHING_DATA'); ?>
            </div>
            <div id="mod_siwecos_results" style="display: none" class="container-fluid">
                <div class="row-fluid">
                    <div class="span4">
                        <div data-size="200" data-width="20" data-style="Arch" data-theme="Red-Gold-Green" data-animate_gauge_colors="1" class="GaugeMeter"></div>
                    </div>
                    <div class="span8">
                        <strong class="text-center"><?php echo JText::_('MOD_SIWECOS_RESULTS_YOUR_SCANNERDETAILS'); ?></strong>
                        <?php echo JText::_('MOD_SIWECOS_LAST_SCAN'); ?> <span id="siwecosLastScan"></span>
                        <div class="row-striped" id="mod_siwecos_scannerlist">
                        </div>
                        <button id="siwecosStartScanBtn" class="btn-primary btn"><?php echo JText::_('MOD_SIWECOS_BUTTON_RESCAN_LABEL'); ?></button>
                        <a href="https://siwecos.de/app/#/domains" target="_blank" class="btn-secondary btn"><?php echo JText::_('MOD_SIWECOS_BUTTON_STATS_LABEL'); ?></a>
                        <a href="<?php echo JRoute::_('index.php?option=com_plugins&view=plugin&task=plugin.edit&layout=edit&extension_id=' . $plugin->id); ?>" class="btn-secondary btn"><?php echo JText::_('MOD_SIWECOS_BUTTON_CONFIGURATION_LABEL'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

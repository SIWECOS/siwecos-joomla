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

$doc = JFactory::getDocument();
$doc->addScript('../media/mod_siwecos/js/mod_siwecos.js');
$doc->addScript('../media/mod_siwecos/js/jquery.AshAlom.gaugeMeter-2.0.0.min.js');
$doc->addStyleSheet('../media/mod_siwecos/css/mod_siwecos.css');

JText::script('MOD_SIWECOS_RESULTS_DOMAIN_NOT_FOUND');
?>

<div class="mod_siwecos <?php echo $moduleclass_sfx ?>">
    <?php if(!$pluginParams->get('authtoken')): ?>
        <div class="mod_siwecos_login">
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
                        <div class="row-striped" id="mod_siwecos_scannerlist">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

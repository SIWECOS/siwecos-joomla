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
$doc->addStyleSheet('../media/mod_siwecos/css/mod_siwecos.css');

JText::script('MOD_SIWECOS_RESULTS_DOMAIN_NOT_FOUND');
?>

<div class="mod_siwecos <?php echo $moduleclass_sfx ?>">
    <?php if(!$params->get('authtoken')): ?>
        <div class="mod_siwecos_login">
            <h5><?php echo JText::_('MOD_SIWECOS_PLEASE_LOGIN'); ?></h5>

            <div class="form-horizontal">
                <div class="control-group">
                    <div class="control-label">
                        <label><?php echo JText::_('MOD_SIWECOS_LOGIN_USERNAME'); ?></label>
                    </div>
                    <div class="controls">
                        <input type="text" id="mod_siwecos_uname" value="">
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label><?php echo JText::_('MOD_SIWECOS_LOGIN_PASSWORD'); ?></label>
                    </div>
                    <div class="controls">
                        <input type="password" id="mod_siwecos_pwd" value="">
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        &nbsp;
                    </div>
                    <div class="controls">
                        <button id="mod_siwecos_login_button" class="btn"><?php echo JText::_('MOD_SIWECOS_LOGIN_BUTTON_LOGIN'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="mod_siwecos_resultbox">
            <div id="mod_siwecos_loadingtext">
                <?php echo JText::_('MOD_SIWECOS_RESULTS_FETCHING_DATA'); ?>
            </div>
            <div id="mod_siwecos_results" style="display: none" class="container-fluid">
                <div class="row-fluid">
                    <div class="span5">
                        <strong class="text-center"><?php echo JText::_('MOD_SIWECOS_RESULTS_YOUR_SECURITYSTATUS'); ?></strong>
                        <div id="mod_siwecos_resultscale">
                            <span id="mod_siwecos_resultneedle"></span>
                        </div>
                    </div>
                    <div class="span7">
                        <strong class="text-center"><?php echo JText::_('MOD_SIWECOS_RESULTS_YOUR_SCANNERDETAILS'); ?></strong>
                        <div class="row-striped" id="mod_siwecos_scannerlist">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

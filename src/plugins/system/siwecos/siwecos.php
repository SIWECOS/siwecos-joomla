<?php
/**
 * @package     SIWECOS.Plugin
 *
 * @copyright   Copyright (C) 2018 eco - Verband der Internetwirtschaft e.V., Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * SIWECOS System Plugin.
 *
 * @since  0.8
 */
class PlgSystemSiwecos extends JPlugin
{
	/**
	 * @var string The API url
	 */
	protected $apiUrl = 'https://bla.staging2.siwecos.de/api/v1';

	/**
	 * Content is passed by reference. Method is called before the content is saved.
	 *
	 * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
	 * @param   object  $table    A JTable object.
	 * @param   bool    $isNew    If the content is just about to be created.
	 * @param   array   $data     The posted data
	 * @return bool
	 * @throws Exception
	 */
	public function onExtensionBeforeSave($context, $table, $isNew, $data)
	{
		if ($context !== 'com_plugins.plugin' && $table->element === 'siwecos') {
			return true;
		}

		if (empty($data['params']['email']) || empty($data['params']['password'])) {
			return true;
		}

		$http = JHttpFactory::getHttp();

		$obj = new stdClass;
		$obj->email = $data['params']['email'];
		$obj->password = $data['params']['password'];

		$send = json_encode($obj);

		$result = $http->post($this->apiUrl .'/users/login', $send, array('Accept'=>'application/json', 'Content-Type' => 'application/json;charset=UTF-8'));

		if ($result->code !== 200) {
			throw new Exception(JText::_('JGLOBAL_AUTH_INVALID_PASS'), $result->code);
		}

		$json = json_decode($result->body);
		if (empty($json->token)) {
			throw new Exception(JText::_('PLG_SYSTEM_SIWECOS_AUTHTOKEN_EMPTY'), 500);
		}

		$tmpparams = json_decode($table->params);
		$tmpparams->authtoken = $json->token;

		unset($tmpparams->password);

		$table->params = json_encode($tmpparams);
	}
}

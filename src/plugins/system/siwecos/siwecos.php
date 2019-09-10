<?php
/**
 * @version    %%PLUGINVERSION%%
 * @package    PlgSiwecos
 * @copyright  Copyright (C) 2017 CMS-Garden e.V.
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.siwecos.de
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
	protected $apiUrl = 'https://bla.siwecos.de/api/v1';

	/**
	 * Content is passed by reference. Method is called before the content is saved.
	 *
	 * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
	 * @param   object  $table    A JTable object.
	 * @param   bool    $isNew    If the content is just about to be created.
	 * @param   array   $data     The posted data
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function onExtensionBeforeSave($context, $table, $isNew=false, $data=array())
	{
		if ($context !== 'com_plugins.plugin' || $table->element !== 'siwecos')
		{
			return true;
		}

		if (empty($data['params']['email']) || empty($data['params']['password']))
		{
			// We never save the password
			$tmpparams = new JRegistry($table->params);
			$tmpparams->set('password', '');
			$table->params = json_encode($tmpparams);

			return true;
		}

		$http = JHttpFactory::getHttp();

		$obj = new stdClass;
		$obj->email = $data['params']['email'];
		$obj->password = $data['params']['password'];

		$send = json_encode($obj);

		$result = $http->post(
			$this->apiUrl . '/users/login',
			$send,
			array('Accept' => 'application/json', 'Content-Type' => 'application/json;charset=UTF-8')
		);

		if ($result->code !== 200)
		{
			throw new Exception(JText::_('JGLOBAL_AUTH_INVALID_PASS'), $result->code);
		}

		$json = json_decode($result->body);

		if (empty($json->token))
		{
			throw new Exception(JText::_('PLG_SYSTEM_SIWECOS_AUTHTOKEN_EMPTY'), 500);
		}

		$tmpparams = new JRegistry($table->params);
		$tmpparams->set('authToken', $json->token);
		$tmpparams->set('password', '');
		$table->params = json_encode($tmpparams);
	}

	/**
	 * Content is passed by reference. Method is called after the content is saved.
	 *
	 * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
	 * @param   object  $table    A JTable object.
	 * @param   bool    $isNew    If the content is just about to be created.
	 * @param   array   $data     The posted data
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 */
	public function onExtensionAfterSave($context, $table, $isNew, $data=array())
	{
		if ($context !== 'com_plugins.plugin' || $table->element !== 'siwecos')
		{
			return true;
		}

		$inputFilter = new \Joomla\Filter\InputFilter;

		$this->params = new JRegistry($table->params);
		$authToken = $this->params->get('authToken');

		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json;charset=UTF-8',
			'userToken'    => $authToken
		);

		$localDomain = JUri::root();
		$localDomain = rtrim($localDomain, '/');

		$http = JHttpFactory::getHttp();

		$result = $http->post($this->apiUrl . '/domains/listDomains', '', $headers);

		if ($result->code !== 200)
		{
			throw new Exception(JText::sprintf('PLG_SYSTEM_SIWECOS_API_ERROR_CODE', $result->code), $result->code);
		}

		$json = json_decode($result->body);

		if (json_last_error() !== JSON_ERROR_NONE)
		{
			throw new Exception(JText::sprintf('PLG_SYSTEM_SIWECOS_API_ERROR_INVALID_JSON', json_last_error_msg()), 500);
		}

		if ($json->hasFailed !== false)
		{
			throw new Exception(JText::_('PLG_SYSTEM_SIWECOS_API_ERROR_FAILED'), $json->code);
		}

		foreach ($json->domains as $domain)
		{
			if ($domain->domain === $localDomain)
			{
				if ($domain->verificationStatus !== true)
				{
					$this->startVerification();
				}

				if ($this->params->get('domainToken', '') != $domain->domainToken)
				{
					$this->params->set('domainToken', $inputFilter->clean($domain->domainToken, '', 'ALNUM'));

					$table->params = json_encode($this->params);

					if (!$table->store())
					{
						throw new Exception(JText::_('JERROR_TABLE_STORE_ERROR'));
					}
				}

				return true;
			}
		}

		// Submit new Domain
		$obj = new stdClass;
		$obj->danger_level = $this->params->get('dangerLevel', 10);
		$obj->domain = $localDomain;

		$sendData = json_encode($obj);

		$result = $http->post($this->apiUrl . '/domains/addNewDomain', $sendData, $headers);

		if ($result->code !== 200)
		{
			throw new Exception(JText::_('PLG_SYSTEM_SIWECOS_API_ERROR_ADD_NEW_DOMAIN'), $result->code);
		}

		$json = json_decode($result->body);

		if ($json->hasFailed !== false)
		{
			throw new Exception(JText::_('PLG_SYSTEM_SIWECOS_API_ERROR_FAILED'), $json->code);
		}

		if ($json->verificationStatus === true)
		{
			return true;
		}

		if (empty($json->domainToken))
		{
			throw new Exception(JText::_('PLG_SYSTEM_SIWECOS_API_ERROR_NO_DOMAIN_TOKEN'), 501);
		}

		$this->params->set('domainToken', $inputFilter->clean($json->domainToken, '', 'ALNUM'));

		$table->params = json_encode($this->params);

		if (!$table->store())
		{
			throw new Exception(JText::_('JERROR_TABLE_STORE_ERROR'));
		}

		$this->startVerification();

		$this->startScan();
	}

	/**
	 * Add SIWECOS Meta tag
	 *
	 * @return void
	 */
	public function onBeforeCompileHead()
	{
		if (!empty($domainToken = $this->params->get('domainToken')))
		{
			$doc = JFactory::getDocument();
			$doc->setMetaData('siwecostoken', $domainToken);
		}
	}

	/**
	 * Ajax call handler for module
	 *
	 * @return array|bool|\Joomla\CMS\Http\Response
	 *
	 * @throws Exception
	 */
	public function onAjaxSiwecos()
	{
        $this->loadLanguage();

		if (!JFactory::getApplication()->isClient('administrator'))
		{
			throw new Exception('PLG_SYSTEM_SIWECOS_ERROR_ONLY_IN_BACKEND', 403);
		}

		if (empty($domainToken = $this->params->get('domainToken')))
		{
			throw new Exception('PLG_SYSTEM_SIWECOS_ERROR_NO_DOMAIN_TOKEN', 404);
		}

		if (empty($domainToken = $this->params->get('authToken')))
		{
			throw new Exception('PLG_SYSTEM_SIWECOS_ERROR_NO_AUTH_TOKEN', 405);
		}

		$input = JFactory::getApplication()->input;
		$method = $input->get('method', '', 'cmd');

		switch ($method)
		{
			case 'domainStatus':
				$return = $this->getDomainStats();
				break;
			case 'domainScan':
				$return = $this->startScan();
				break;
			default:
				$return = false;
		}

		return $return;
	}

	/**
	 * Request the stats for the domain
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDomainStats()
	{
		$authToken = $this->params->get('authToken');

		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json;charset=UTF-8',
			'userToken'    => $authToken
		);

		$localDomain = JUri::root();
		$localDomain = rtrim($localDomain, '/');
		$http        = JHttpFactory::getHttp();

		$result = $http->get($this->apiUrl . '/scan/result?domain=' . $localDomain, $headers);

		if ($result->code !== 200)
		{
			throw new Exception(JText::sprintf('PLG_SYSTEM_SIWECOS_API_ERROR_CODE', $result->code), $result->code);
		}

		$json = json_decode($result->body);

		if (json_last_error() !== JSON_ERROR_NONE)
		{
			throw new Exception(JText::sprintf('PLG_SYSTEM_SIWECOS_API_ERROR_JSON_MSG', json_last_error_msg()), 500);
		}

		$date = JFactory::getDate($json->scanFinished->date, $json->scanFinished->timezone);
		$json->scanFinished->localDate = $date->format(JText::_('DATE_FORMAT_LC5'));

		$return = array(
			'code' => 200,
			'result' => $json
		);

		return $return;
	}

	/**
	 * Starts the scan for the current Domain
	 *
	 * @return string call result
	 */
	public function startScan()
	{
		$localDomain = JUri::root();
		$localDomain = rtrim($localDomain, '/');

		// Submit new Domain
		$obj = new stdClass;
		$obj->dangerLevel = $this->params->get('dangerLevel', 10);
		$obj->domain = $localDomain;

		$sendData = json_encode($obj);

		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json;charset=UTF-8',
			'userToken'    => $this->params->get('authToken', 10)
		);

		$http = JHttpFactory::getHttp();

		$result = $http->post($this->apiUrl . '/scan/start', $sendData, $headers);

		return $result;
	}

	/**
	 * Starts the scan for the current Domain
	 *
	 * @return void
	 */
	public function startVerification()
	{

		$localDomain = JUri::root();
		$localDomain = rtrim($localDomain, '/');

		// Submit new Domain
		$obj = new stdClass;
		$obj->danger_level = $this->params->get('dangerLevel', 10);
		$obj->domain = $localDomain;

		$sendData = json_encode($obj);

		$headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json;charset=UTF-8',
			'userToken'    => $this->params->get('authToken', 10)
		);

		$http = JHttpFactory::getHttp();

		$http->post($this->apiUrl . '/domains/verifyDomain', $sendData, $headers);
	}
}

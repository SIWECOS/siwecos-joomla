<?php
/**
 * @version    %%MODULEVERSION%%
 * @package    ModSiwecos
 * @copyright  Copyright (C) 2017 CMS-Garden e.V.
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.siwecos.de
 */

/**
 * Class ModSiwecosHelper
 *
 * @since  1.0.0
 */
class ModSiwecosHelper
{
    const API_URL = "https://www.siwecos.de/wp-json/siwecos/v1/";

    /**
     * Try to login a user against the SIWECOS API
     *
     * @return array
     */
    public static function loginAjax()
    {
        $input = JFactory::getApplication()->input;

        $data = array(
            "username" => $input->get('uname', '', 'raw'),
            "password" => $input->get('pwd', '', 'raw')
        );

        $response = self::doRequest("POST", "user/login", $data);

        if ($response->code === 200 && !empty($response->data) && !empty($response->data->authcode))
        {
            $module = JModuleHelper::getModule('mod_siwecos');

            $params = new \Joomla\Registry\Registry;
            $params->loadString($module->params);
            $params->set('authtoken', $response->data->authcode);

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update('#__modules AS m')
                ->set('m.params = ' . $db->quote((string) $params))
                ->where('m.module = "mod_siwecos"');
            $db->setQuery($query);
            $db->execute();
        }

        return $response;
    }

    /**
     * Fetch domain security status from API
     *
     * @return array
     */
    public static function domainStatusAjax()
    {
        $module = JModuleHelper::getModule('mod_siwecos');
        $params = new \Joomla\Registry\Registry;
        $params->loadString($module->params);

        $response = self::doRequest("GET", "domain/list/" . $params->get("authtoken"));

        if (empty($response->collection) || count($response->collection) == 0)
        {
            return ["code" => 404, "message" => $response->description];
        }

        $domainId = false;

        foreach ($response->collection as $domain)
        {
            if ($domain->address === rtrim(JUri::root(), "/"))
            {
                $domainId = $domain->id;
            }
        }

        if (!$domainId)
        {
            return ["code" => 404, "message" => JText::_('No match')];
        }

        $response = self::doRequest("GET", "internal/scanner/" . $domainId);

        if (empty($response->message) || $response->message !== "ok")
        {
            return ["code" => 500, "message" => JText::_('Invalid response')];
        }

        return ["code" => 200, "result" => $response->collection];
    }

    /**
     * Do a request against the SIWECOS API
     *
     * @param   string  $method    HTTP method
     * @param   string  $endpoint  URL endpoint
     * @param   array   $data      dataset for request
     *
     * @return mixed
     */
    public static function doRequest($method, $endpoint, $data = array())
    {
        $http = JHttpFactory::getHttp();

        switch ($method)
        {
            case "POST":
                $response = $http->post(self::API_URL . $endpoint, $data);
                break;

            case "GET":
                $response = $http->get(self::API_URL . $endpoint);
                break;

            default:
                throw new RuntimeException("Invalid HTTP method");
                break;

        }

        $body = json_decode($response->body);

        if ($body === null || json_last_error() !== JSON_ERROR_NONE)
        {
            throw new RuntimeException("SIWECOS API Error");
        }

        return $body;
    }
}

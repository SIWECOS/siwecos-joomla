<?php
/**
 * @version    %%MODULEVERSION%%
 * @package    ModSiwecos
 * @copyright  Copyright (C) 2017 CMS-Garden e.V.
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link       http://www.siwecos.de
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class Pkg_SiwecosInstallerScript
{
	/**
	 * The name of our package, e.g. pkg_example. Used for dependency tracking.
	 *
	 * @var  string
	 */
	protected $packageName = 'pkg_siwecos';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.4.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.8.0';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '4.9.999';

	/**
	 * =================================================================================================================
	 * DO NOT EDIT BELOW THIS LINE
	 * =================================================================================================================
	 */

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the package. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * In here we'll try to install FOF. We have to do that before installing the component since it's using an
	 * installation script extending FOF's InstallScript class. We can't use a <file> tag in the manifest to install FOF
	 * since the FOF installation is expected to fail if a newer version of FOF is already installed on the site.
	 *
	 * @param   string                     $type    Installation type (install, update, discover_install)
	 * @param   \JInstallerAdapterPackage  $parent  Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight($type, $parent)
	{
		// Check the minimum PHP version
		if (!version_compare(PHP_VERSION, $this->minimumPHPVersion, 'ge'))
		{
			$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this package</p>";
			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// Check the minimum Joomla! version
		if (!version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";
			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// Check the maximum Joomla! version
		if (!version_compare(JVERSION, $this->maximumJoomlaVersion, 'le'))
		{
			$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";
			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string                       $type   install, update or discover_update
	 * @param   \JInstallerAdapterComponent  $parent Parent object
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		/**
		 * Clean the cache after installing the package.
		 *
		 * See bug report https://github.com/joomla/joomla-cms/issues/16147
		 */
		$conf = \JFactory::getConfig();
		$clearGroups = array('_system', 'com_modules', 'mod_menu', 'com_plugins', 'com_modules');
		$cacheClients = array(0, 1);

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $clientId)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase' => ($clientId) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache')
					);

					/** @var JCache $cache */
					$cache = \JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $exception)
				{
					$options['result'] = false;
				}

				// Trigger the onContentCleanCache event.
				try
				{
					JFactory::getApplication()->triggerEvent('onContentCleanCache', $options);
				}
				catch (Exception $e)
				{
					// Suck it up
				}
			}
		}
	}

	/**
	 * Tuns on installation (but not on upgrade). This happens in install and discover_install installation routes.
	 *
	 * @param   \JInstallerAdapterPackage  $parent  Parent object
	 *
	 * @return  boolean
	 */
	public function install($parent)
	{
		// Enable the extensions we need to install
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->update('#__extensions')
			->set('enabled = 1')
			->where($db->qn('type') . " = 'plugin'")
			->where($db->qn('element') . " = 'siwecos'");

		$db->setQuery($query)->execute();

		$query = $db->getQuery(true);
		$query->update('#__modules')
			->set('published = 1')
			->set('access = 3')
			->set('position = "cpanel"')
			->set('ordering = 1')
			->where($db->qn('module') . " = 'mod_siwecos'");

		$db->setQuery($query)->execute();

		// Add menu mapping if not present
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__modules')
			->where($db->qn('module') . " = 'mod_siwecos'");

		$moduleId = $db->setQuery($query)->loadResult();

		$query = $db->getQuery(true);
		$query->select('menuid')
			->from('#__modules_menu')
			->where('moduleid = ' . (int) $moduleId);

		$exists = $db->setQuery($query)->loadResult();

		if ($exists !== null)
		{
			return true;
		}

		$query = $db->getQuery(true);
		$query->insert('#__modules_menu')
			->columns($db->quoteName(["moduleid", "menuid"]))
			->values($moduleId . ", 0");

		$db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   \JInstallerAdapterPackage  $parent  Parent object
	 *
	 * @return  boolean
	 */
	public function uninstall($parent)
	{
		return true;
	}
}

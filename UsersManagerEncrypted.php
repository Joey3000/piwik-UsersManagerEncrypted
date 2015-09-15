<?php
/**
 * UsersManagerEncrypted plugin for Piwik, the free/libre analytics platform
 *
 * @author  Joey3000 https://github.com/Joey3000
 * @link    https://github.com/Joey3000/piwik-UsersManagerEncrypted
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManagerEncrypted;

use Exception;
use Piwik\Piwik;
use Piwik\Plugin\Manager;

class UsersManagerEncrypted extends \Piwik\Plugins\UsersManager\UsersManager
{
    /**
     * Return list of plug-in specific JavaScript files to be imported by the asset manager
     *
     * @see Piwik\AssetManager
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UsersManagerEncrypted/javascripts/usersManager.js";
        $jsFiles[] = "plugins/UsersManagerEncrypted/javascripts/usersSettings.js";
    }

    /**
     * Called on plugin activation.
     */
    public function activate()
    {
        // don't allow plugin activation if LoginEncrypted is inactive
        if (Manager::getInstance()->isPluginActivated("LoginEncrypted") == false) {
            throw new Exception(Piwik::translate('UsersManagerEncrypted_LoginEncryptedInactive'));
        } else {
            // deactivate default UsersManager module, as both cannot be activated together
            if (Manager::getInstance()->isPluginActivated("UsersManager") == true) {
                Manager::getInstance()->deactivatePlugin("UsersManager");
            }
        }
    }

    /**
     * Called on plugin deactivation.
     */
    public function deactivate()
    {
        // activate default UsersManager module, as one of them is needed to access Piwik
        if (Manager::getInstance()->isPluginActivated("UsersManager") == false) {
            Manager::getInstance()->activatePlugin("UsersManager");
        }
    }
}

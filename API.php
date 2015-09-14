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

use Piwik\Plugins\LoginEncrypted\Controller as LoginEncrypted_Controller;

class API extends \Piwik\Plugins\UsersManager\API
{
    /**
     * Decrypts the password and calls the original function on the decrypted value.
     *
     * @see the parent class function for parameters and return value
     */
    public function addUser($userLogin, $password, $email, $alias = false, $_isPasswordHashed = false)
    {
        $password = LoginEncrypted_Controller::decryptPassword($password);

        return parent::addUser($userLogin, $password, $email, $alias, $_isPasswordHashed);
    }

    /**
     * Decrypts the password (if encrypted) and calls the original function on
     * the decrypted value.
     *
     * @see the parent class function for parameters and return value
     */
    public function updateUser($userLogin, $password = false, $email = false, $alias = false,
                               $_isPasswordHashed = false, $directCall = false)
    {
        // check if this function is called directly
        // Reason: updateUser() is called in following situations:
        //         1. With an already decrypted password by:
        //            * Piwik\Plugins\Login\PasswordResetter::confirmNewPassword()
        //              on password change via the form before login
        //            * Controller::processPasswordChange() when any user changes
        //              their own password in their account settings
        //         2. With an encrypted password when called directly by (so,
        //            decryption is needed in this case):
        //            * /plugins/UsersManagerEncrypted/javascripts/usersManager.js::sendUpdateUserAJAX()
        //              when a super user changes someone's password in Piwik user administration.
        if($directCall == 'true') {
            $password = LoginEncrypted_Controller::decryptPassword($password);
        }

        return parent::updateUser($userLogin, $password, $email, $alias, $_isPasswordHashed);
    }
}

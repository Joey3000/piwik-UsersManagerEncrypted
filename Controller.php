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

use Piwik\Common;
use Piwik\Plugins\LoginEncrypted\Controller as LoginEncrypted_Controller;

class Controller extends \Piwik\Plugins\UsersManager\Controller
{
    /**
     * Gets the password from HTTP request variable, decrypts it and writes the decrypted
     * value back into the _POST request.
     * Note: Writing to _POST directly, as there doesn't seem to be another way,
     *       because the parent function will re-read from request (i.e. _POST).
     *
     * @see the parent class function for parameters and return value
     */
    protected function processPasswordChange($userLogin)
    {
        $password = Common::getRequestvar('password', false);
        $password = LoginEncrypted_Controller::decryptPassword($password);

        // write out if a password was submitted
        // Note: Compare loosely, so both, "" (password input empty; forms send strings)
        //       and "password input not sent" are covered - see
        //       https://secure.php.net/manual/en/types.comparisons.php
        if($password != "") {
            $_POST['password'] = $password;
        }

        $passwordBis = Common::getRequestvar('passwordBis', false);
        $passwordBis = LoginEncrypted_Controller::decryptPassword($passwordBis);

        // write out if a password confirmation was submitted
        if($passwordBis != "") {
            $_POST['passwordBis'] = $passwordBis;
        }

        // call the original function on the decrypted values
        return parent::processPasswordChange($userLogin);
    }
}

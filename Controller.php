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
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\LoginEncrypted\Controller as LoginEncrypted_Controller;
use Piwik\Plugins\UsersManager\Model;

class Controller extends \Piwik\Plugins\UsersManager\Controller
{
    /**
     * Checks if the provided CURRENT password is correct and calls the parent
     * class function if so. Otherwise provides error message.
     *
     * @see the parent class function for parameters and return value
     */
    public function recordUserSettings()
    {
        try {
            $passwordCurrent = Common::getRequestvar('passwordCurrent', false);
            $passwordCurrent = LoginEncrypted_Controller::decryptPassword($passwordCurrent);

            // Note: Compare loosely, so both, "" (password input empty; forms send strings)
            //       and "password input not sent" are covered - see
            //       https://secure.php.net/manual/en/types.comparisons.php
            if ($passwordCurrent != "") {
                $userName = Piwik::getCurrentUserLogin(); // gets username as string or "anonymous"

                // see Piwik\Plugins\Login\Auth for used password hash function
                // (in setPassword()) and access to hashed password (in getTokenAuthSecret())
                if ($userName != 'anonymous') {
                    $model = new Model;
                    $user = $model->getUser($userName);
                    if (UsersManagerEncrypted::getPasswordHash($passwordCurrent) === $user['password']) {
                        $toReturn = parent::recordUserSettings();
                    } else {
                        throw new Exception(Piwik::translate('UsersManagerEncrypted_CurrentPasswordIncorrect'));
                    }
                } else {
                    throw new Exception(Piwik::translate('UsersManagerEncrypted_UserNotAuthenticated'));
                }
            } else {
                throw new Exception(Piwik::translate('UsersManagerEncrypted_CurrentPasswordNotProvided'));
            }
        } catch (Exception $e) {
            $response = new ResponseBuilder(Common::getRequestVar('format'));
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    /**
     * Gets the NEW password from HTTP request variable, decrypts it and writes the decrypted
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
        if ($password != "") {
            $_POST['password'] = $password;
        }

        $passwordBis = Common::getRequestvar('passwordBis', false);
        $passwordBis = LoginEncrypted_Controller::decryptPassword($passwordBis);

        // write out if a password confirmation was submitted
        if ($passwordBis != "") {
            $_POST['passwordBis'] = $passwordBis;
        }

        // call the original function on the decrypted values
        return parent::processPasswordChange($userLogin);
    }
}

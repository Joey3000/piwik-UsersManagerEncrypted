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
use Piwik\Plugins\LoginEncrypted\Crypto;
use Piwik\Plugins\LoginEncrypted\CryptoForm;
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
            $passwordCurrent = Crypto::decrypt($passwordCurrent);

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
     * Gets the NEW password from HTTP request parameter, decrypts it and writes
     * the decrypted value back into _POST request.
     * Note: Writing to _POST directly, as there doesn't seem to be another way,
     *       because the parent function will re-read from request (i.e. _POST).
     *
     * @see the parent class function for parameters and return value
     */
    protected function processPasswordChange($userLogin)
    {
        $password = Common::getRequestvar('password', false);
        CryptoForm::decryptAndWriteToPost('password', $password);

        $passwordBis = Common::getRequestvar('passwordBis', false);
        CryptoForm::decryptAndWriteToPost('passwordBis', $passwordBis);

        // call the original function on the decrypted values
        return parent::processPasswordChange($userLogin);
    }
}

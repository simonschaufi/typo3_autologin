<?php

declare(strict_types=1);

namespace SimonSchaufi\Autologin\Service;

use TYPO3\CMS\Core\Authentication\AuthenticationService;
use TYPO3\CMS\Core\Registry;

class AutoLoginService extends AuthenticationService
{
    public function __construct(
        private readonly Registry $registry
    ) {
    }

    /**
     * Find a user (eg. look up the user record in database when a login is sent)
     *
     * @return array|null User array or null
     */
    public function getUser(): ?array
    {
        session_start();
        $hmac = $_SESSION['typo3-autologin-user'] ?? null;
        unset($_SESSION['typo3-autologin-user']);
        if ($hmac === null) {
            return null;
        }

        $userId = (int)$this->registry->get('typo3-autologin-hmac', $hmac);
        $this->registry->remove('typo3-autologin', $hmac);

        $dbUserSetup = [...$this->db_user, 'username_column' => 'uid', 'enable_clause' => ''];
        $user = $this->fetchUserRecord($userId, '', $dbUserSetup);

        if (!empty($user)) {
            $user['typo3-autologin-autoload'] = true;
        }

        return is_array($user) ? $user : null;
    }

    /**
     * Authenticate a user based on a value set in session before redirect
     *
     * @param array $user Data of user.
     *
     * @return int >= 200: User authenticated successfully.
     *                     No more checking is needed by other auth services.
     *             >= 100: User not authenticated; this service is not responsible.
     *                     Other auth services will be asked.
     *             > 0:    User authenticated successfully.
     *                     Other auth services will still be asked.
     *             <= 0:   Authentication failed, no more checking needed
     *                     by other auth services.
     */
    public function authUser(array $user): int
    {
        return ($user['typo3-autologin-autoload'] ?? false) ? 200 : 100;
    }
}

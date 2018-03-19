<?php
/**
 * @author Alexandre (DaazKu) Chouinard <alexandre.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace Vanilla\Models;


use \Exception;
use Garden\Container\Container;
use Garden\Web\Exception\ServerException;
use Garden\Web\Exception\NotFoundException;
use Vanilla\AddonManager;
use Vanilla\Authenticator\Authenticator;

class AuthenticatorModel {

    /** @var Container */
    private $container;

    /** @var AddonManager */
    private $addonManager;

    /**
     * AuthenticatorModel constructor.
     *
     * @param Container $container
     * @param AddonManager $addonManager
     */
    public function __construct(Container $container, AddonManager $addonManager) {
        $this->container = $container;
        $this->addonManager = $addonManager;
    }

    /**
     * Get an authenticator.
     *
     * @param string $authenticatorType
     * @param string $authenticatorID
     * @return Authenticator
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getAuthenticator($authenticatorType, $authenticatorID) {
        if (empty($authenticatorType)) {
            throw new NotFoundException();
        }

        $authenticatorClassName = $authenticatorType.'Authenticator';

        /** @var Authenticator $authenticatorInstance */
        $authenticatorInstance = null;

        // Check if the container can find the authenticator.
        try {
            $authenticatorInstance = $this->container->getArgs($authenticatorClassName, [$authenticatorID]);
            return $authenticatorInstance;
        } catch (Exception $e) {}

        // Use the addonManager to find the class.
        $authenticatorClasses = $this->addonManager->findClasses("*\\$authenticatorClassName");

        if (empty($authenticatorClasses)) {
            throw new NotFoundException($authenticatorClassName);
        }

        // Throw an exception if there are multiple authenticators with that type.
        // We are not handling authenticators with the same name in different namespaces for now.
        if (count($authenticatorClasses) > 1) {
            throw new ServerException(
                "Multiple class named \"$authenticatorClasses\" have been found.",
                500,
                ['classes' => $authenticatorClasses]
            );
        }

        $fqnAuthenticationClass = $authenticatorClasses[0];

        if (!is_a($fqnAuthenticationClass, Authenticator::class, true)) {
            throw new ServerException(
                "\"$fqnAuthenticationClass\" is not an ".Authenticator::class,
                500
            );
        }

        $authenticatorInstance = $this->container->getArgs($fqnAuthenticationClass, [$authenticatorID]);

        return $authenticatorInstance;
    }
}

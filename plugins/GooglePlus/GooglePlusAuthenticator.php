<?php
/**
 * @author Alexandre (DaazKu) Chouinard <alexandre.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

use \Vanilla\Authenticator\ShimAuthenticator;

/**
 * Class GoogleAuthenticator.
 */
class GooglePlusAuthenticator extends ShimAuthenticator {

    /**
     * GoogleAuthenticator constructor.
     *
     * @param string $authenticatorID
     *
     * @throws \Garden\Schema\ValidationException
     */
    public function __construct(string $authenticatorID) {
        parent::__construct('GooglePlus');
    }

    /**
     * @inheritDoc
     */
    protected static function getAuthenticatorTypeInfoImpl(): array {
        return [
            'ui' => [
                'photoUrl' => '/applications/dashboard/design/images/authenticators/google.svg',
                'backgroundColor' => '#fff',
                'foregroundColor' => '#000',
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function isUnique(): bool {
        return true;
    }

    /**
     * {@link Authenticator::getAuthenticatorInfoImpl()}
     */
    protected function getAuthenticatorInfoImpl(): array {
        return [
            'ui' => [
                'url' => '/entry/connect/googlePlusAuthRedirect',
                'buttonName' => 'Sign in with Google',
            ],
        ];
    }
}

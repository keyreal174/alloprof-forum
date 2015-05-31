<?php
/**
 * Convenience access to current user's session.
 *
 * @copyright 2008-2015 Vanilla Forums, Inc
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Dashboard
 * @since 2.0
 */

/**
 * Handles /session endpoint.
 */
class SessionController extends DashboardController {

    /**
     * Stash a value in the user's session, or unstash it if no value was provided to stash.
     *
     * Looks for Name and Value POST/GET variables to pass along to Gdn_Session.
     */
    public function Stash() {
        $this->DeliveryType(DELIVERY_TYPE_BOOL);
        $this->DeliveryMethod(DELIVERY_METHOD_JSON);
        $Name = TrueStripSlashes(GetValue('Name', $_POST, ''));
        $Value = TrueStripSlashes(GetValue('Value', $_POST, ''));
        $Response = Gdn::Session()->Stash($Name, $Value);
        if ($Name != '' && $Value == '')
            $this->SetJson('Unstash', $Response);

        $this->Render();
    }
}

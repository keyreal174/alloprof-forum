/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import { t } from "@dashboard/application";
import React from "react";
import { Link } from "react-router-dom";

export default class RememberPasswordLink extends React.Component {
    public render() {
        return (
            <p className="authenticateUser-paragraph isCentered">
                {t("Remember your password?")} <Link to="/authenticate/signin">{t("Sign In")}</Link>
            </p>
        );
    }
}

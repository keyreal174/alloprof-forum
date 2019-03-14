/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { IMe, LoadStatus } from "@library/@types/api";
import ReduxActions from "@library/redux/ReduxActions";

/**
 * Redux actions for the users data.
 */
export default class UsersActions extends ReduxActions {
    public static readonly GET_ME_REQUEST = "@@users/GET_ME_REQUEST";
    public static readonly GET_ME_RESPONSE = "@@users/GET_ME_RESPONSE";
    public static readonly GET_ME_ERROR = "@@users/GET_ME_ERROR";

    public static readonly ACTION_TYPES: ActionsUnion<typeof UsersActions.getMeACs>;

    public static getMeACs = ReduxActions.generateApiActionCreators(
        UsersActions.GET_ME_REQUEST,
        UsersActions.GET_ME_RESPONSE,
        UsersActions.GET_ME_ERROR,
        {} as IMe,
        {},
    );

    public getMe = async () => {
        const currentUser = this.getState().users.current;
        if (currentUser.status === LoadStatus.LOADING) {
            // Don't request the user more than once.
            return;
        }
        return await this.dispatchApi("get", "/users/me", UsersActions.getMeACs, {});
    };
}

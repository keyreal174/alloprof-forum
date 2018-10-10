/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license Proprietary
 */

import { IAction } from "@library/state/ReduxActions";

/**
 * Base class for creating a redux reducer.
 */
export default abstract class ReduxReducer<S> {
    /**
     * The initial state of the object.
     */
    public abstract readonly initialState: S;

    /**
     * The reducer function for redux.
     */
    public abstract reducer: (state: S, action: IAction<any>) => S;
}

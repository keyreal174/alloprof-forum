/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 */

import { createStore, compose, applyMiddleware, combineReducers, Store } from "redux";
import { getReducers } from "./reducerRegistry";
import thunk from "redux-thunk";
import IState from "@dashboard/state/IState";
import {log} from "@dashboard/utility";

// There may be an initial state to import.
const initialState = {};
const initialActions = window.__ACTIONS__ || [];

const middleware = [thunk];

// Browser may have redux dev tools installed, if so integrate with it.
const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(applyMiddleware(...middleware));

// Build the store, add devtools extension support if it's available.
let store: Store<IState> | undefined;

export default function getStore<S extends IState = IState>() {
    if (store === undefined) {
        // Get our reducers.
        const reducer = combineReducers(getReducers());

        log("createStore()");
        store = createStore(reducer, initialState, enhancer);

        // Dispatch initial actions returned from the server.
        initialActions.forEach(store.dispatch);
    }

    return store;
}

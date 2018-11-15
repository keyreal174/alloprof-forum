/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import qs from "qs";
import { withRouter, RouteComponentProps } from "react-router";
import isEqual from "lodash/isEqual";
import throttle from "lodash/throttle";

interface IProps extends RouteComponentProps<any> {
    value: {
        [key: string]: any;
    };
}

/**
 * Component for automatically peristing it's props into the window's querystring.
 */
class QueryString extends React.Component<IProps> {
    public render(): React.ReactNode {
        return null;
    }

    public componentWillMount() {
        this.updateQueryString();
    }

    public componentDidUpdate(prevProps: IProps) {
        if (!isEqual(prevProps.value, this.props.value)) {
            this.updateQueryString();
        }
    }

    /**
     * Update the query string of the window.
     *
     * This is throttle and put in request animation frame so that it does not take priority over the UI.
     */
    private updateQueryString = throttle(() => {
        requestAnimationFrame(() => {
            const query = qs.stringify(this.props.value);
            this.props.history.replace({
                ...this.props.location,
                search: query,
            });
        });
    }, 100);
}

export default withRouter(QueryString);

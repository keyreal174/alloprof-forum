/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { t } from "@library/application";
import LinkAsButton from "@library/components/LinkAsButton";
import { ILinkPages } from "@library/simplePager/SimplePagerModel";
import classNames from "classnames";
import * as React from "react";

interface IProps {
    url: string;
    pages: ILinkPages;
}

/**
 * Basic pagination. Only previous/next buttons are included.
 */
export default class SimplePager extends React.Component<IProps> {
    public render() {
        const { next, prev } = this.props.pages;

        const buttons = [] as JSX.Element[];
        const isSingle = (prev && !next) || (!prev && next);

        return (
            <div className="simplePager">
                {prev && (
                    <LinkAsButton
                        className={classNames(["simplePager-button", "simplePager-prev", { isSingle }])}
                        key="simplePagerPrev"
                        to={this.makeUrl(prev)}
                    >
                        {t("Previous")}
                    </LinkAsButton>
                )}
                {next && (
                    <LinkAsButton
                        className={classNames(["simplePager-button", "simplePager-next", { isSingle }])}
                        key="simplePagerNext"
                        to={this.makeUrl(next)}
                    >
                        {t("Next")}
                    </LinkAsButton>
                )}
            </div>
        );
    }

    private makeUrl(page: number): string {
        const { url } = this.props;
        return url.replace(":page:", page.toString());
    }
}

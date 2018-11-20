/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";
import { ITab } from "@library/components/tabs/Tabs";
import Button, { ButtonBaseClass } from "@library/components/forms/Button";

interface ITabPanel {
    className?: string;
}

interface IProps {
    tabs: ITabPanel[];
    className?: string;
    selectedTab: number;
    getTabFlapID: (index: number) => string;
    getTabPanelID: (index: number) => string;
}

/**
 * Clean up conditional renders with this component
 */
export default class TabPanels extends React.Component<IProps> {
    public render() {
        const { className, tabs, selectedTab, getTabFlapID, getTabPanelID } = this.props;
        const content = tabs.map((tab: ITabPanel, index) => {
            const key = `tabPanel-${index}`;
            return selectedTab === index ? (
                <div
                    id={getTabPanelID(index)}
                    aria-labelledby={getTabFlapID(index)}
                    role="tabpanel"
                    className={classNames("tabPanel", className)}
                    tabIndex={0}
                    key={key}
                >
                    {content}
                </div>
            ) : (
                <React.Fragment key={key} />
            );
        });
        return <div className={classNames("tabPanels", className)}>{content}</div>;
    }
}

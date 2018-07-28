/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import React from "react";
import classnames from "classnames";

export interface IProps {
    icon: JSX.Element;
    label: string;
    onClick: (event?: React.MouseEvent<HTMLButtonElement>) => void;
    isActive: boolean;
    isDisabled?: boolean;
}

export default class MenuItem extends React.PureComponent<IProps> {
    public render() {
        const { label, isDisabled, isActive, onClick, icon } = this.props;
        const buttonClasses = classnames("richEditor-button", "richEditor-formatButton", "richEditor-menuItem", {
            isActive,
        });

        return (
            <button
                className={buttonClasses}
                type="button"
                role="menuitem"
                aria-label={label}
                aria-pressed={isActive}
                onClick={onClick}
                disabled={isDisabled}
            >
                {icon}
            </button>
        );
    }
}

/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";

import classNames from "classnames";
import { ButtonBaseClass } from "@library/components/forms/Button";
import { t } from "@library/application";
import { clear } from "@library/components/icons/common";

/**
 * Overwrite for the clearIndicator component in React Select
 * Note that this is NOT a true react component and gets called within the react select plugin
 * @param children - children of component
 * @param props - props of component
 */
export function clearIndicator({ children, ...props }) {
    const {
        innerProps: { ref, ...restInnerProps },
        isDisabled,
    } = props;

    // We need to bind the function to the props for that component
    const handleKeyDown = event => {
        switch (event.key) {
            case "Enter":
            case "Spacebar":
            case " ":
                restInnerProps.onMouseDown(event);
                break;
        }
    };

    return (
        <button
            {...restInnerProps}
            className={classNames(ButtonBaseClass.ICON, `${props.prefix}-clear`, "suggestedTextInput-clear")}
            type="button"
            ref={ref}
            style={{}}
            aria-hidden={null} // Unset the prop in restInnerProps
            onKeyDown={handleKeyDown}
            onClick={restInnerProps.onMouseDown}
            onTouchEnd={restInnerProps.onTouchEnd}
            disabled={isDisabled}
            title={t("Clear")}
            aria-label={t("Clear")}
        >
            {clear()}
        </button>
    );
}

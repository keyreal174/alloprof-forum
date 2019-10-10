/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import IndependentSearch from "@library/features/search/IndependentSearch";
import { buttonClasses, ButtonTypes } from "@library/forms/buttonStyles";
import Container from "@library/layout/components/Container";
import { Devices, IDeviceProps, withDevice } from "@library/layout/DeviceContext";
import FlexSpacer from "@library/layout/FlexSpacer";
import Heading from "@library/layout/Heading";
import { PanelWidgetHorizontalPadding } from "@library/layout/PanelLayout";
import { splashClasses, splashVariables } from "@library/splash/splashStyles";
import { t } from "@library/utility/appUtils";
import classNames from "classnames";
import React, { cloneElement, ReactElement, useState } from "react";
import Tooltip, { useTooltip, TooltipPopup } from "@reach/tooltip";
import { ColorValues } from "@library/styles/styleHelpersColors";
import { url } from "csx";
import ReactDOM from "react-dom";
import { mountModal } from "@library/modal/Modal";
import { ConvertDiscussionModal } from "@knowledge/articleDiscussion/ConvertDiscussionModal";
import Portal from "@reach/portal";
import { toolTipClasses, tooltipVariables } from "@library/toolTip/toolTipStyles";
import { NestedCSSProperties } from "typestyle/lib/types";
import { globalVariables } from "@library/styles/globalStyleVars";

const nubPosition = (triggerRect, hasOverflow) => {
    const toolTipVars = tooltipVariables();
    const globalVars = globalVariables();

    const overTriggerPosition =
        triggerRect.top - toolTipVars.nub.width * 2 + globalVars.border.width * 2 + window.scrollY;
    const underTriggerPosition = triggerRect.bottom - globalVars.border.width * 2 + window.scrollY;

    return {
        left: triggerRect.left + triggerRect.width / 2 - toolTipVars.nub.width,
        top: hasOverflow ? overTriggerPosition : underTriggerPosition,
    };
};

function TriangleTooltip(props: { children: React.ReactNode; label: string; ariaLabel?: string }) {
    const globalVars = globalVariables();
    const { children, label, ariaLabel } = props;

    // get the props from useTooltip
    const [trigger, tooltip] = useTooltip();

    // destructure off what we need to position the triangle
    const { isVisible, triggerRect } = tooltip;

    const [hasOverflow, setHasOverflow] = useState(false);
    const classes = toolTipClasses();
    const toolTipVars = tooltipVariables();
    const borderOffset = globalVars.border.width * 2;

    const toolBoxPosition = (triggerRect, tooltipRect) => {
        const triangleHeight = toolTipVars.nub.width / 2;
        const triggerCenter = triggerRect.left + triggerRect.width / 2;
        const left = triggerCenter - tooltipRect.width / 2;
        const maxLeft = window.innerWidth - tooltipRect.width - 2;
        const hasOverflow = triggerRect.bottom + tooltipRect.height + triangleHeight > window.innerHeight;

        setHasOverflow(hasOverflow);

        const overTriggerPosition =
            triggerRect.top - tooltipRect.height + borderOffset - toolTipVars.nub.width + window.scrollY;
        const underTriggerPosition = triggerRect.bottom - borderOffset + toolTipVars.nub.width + window.scrollY;

        return {
            position: "absolute",
            left: Math.min(Math.max(2, left), maxLeft) + window.scrollX,
            top: hasOverflow ? overTriggerPosition : underTriggerPosition,
        };
    };

    return (
        <>
            {cloneElement(children as any, trigger)}
            {isVisible && triggerRect && (
                // The Triangle. We position it relative to the trigger, not the popup
                // so that collisions don't have a triangle pointing off to nowhere.
                // Using a Portal may seem a little extreme, but we can keep the
                // positioning logic simpler here instead of needing to consider
                // the popup's position relative to the trigger and collisions
                <Portal>
                    <div className={classes.nubPosition} style={nubPosition(triggerRect, hasOverflow) as any}>
                        <div className={classNames(classes.nub, hasOverflow ? "isDown" : "isUp")} />
                    </div>
                </Portal>
            )}
            <TooltipPopup
                {...tooltip}
                label={label}
                ariaLabel={ariaLabel ? ariaLabel : label}
                position={toolBoxPosition}
                className={classes.box}
            />
        </>
    );
}

export function ToolTip(props: { children: React.ReactNode; label: string; ariaLabel?: string }) {
    const { children, label, ariaLabel } = props;

    return (
        <TriangleTooltip label={label} ariaLabel={ariaLabel ? ariaLabel : label}>
            {children}
        </TriangleTooltip>
    );
}

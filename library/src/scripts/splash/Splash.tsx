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
import { splashStyles, splashVariables } from "@library/splash/splashStyles";
import { t } from "@library/utility/appUtils";
import classNames from "classnames";
import React from "react";

interface IProps extends IDeviceProps {
    action?: React.ReactNode;
    title?: string; // Often the message to display isn't the real H1
    className?: string;
}

/**
 * A component representing a single crumb in a breadcrumb component.
 */
export class Splash extends React.Component<IProps> {
    public render() {
        const classes = splashStyles();
        const vars = splashVariables();
        const { action, className } = this.props;
        const title = this.props.title;

        return (
            <div className={classNames(className, classes.root)}>
                <div className={classes.outerBackground} />
                <Container>
                    <div className={classes.innerContainer}>
                        <PanelWidgetHorizontalPadding>
                            <div className={classes.titleWrap}>
                                <FlexSpacer className={classes.titleFlexSpacer} />
                                {title && <Heading title={title} className={classes.title} />}
                                <div className={classNames(classes.text, classes.titleFlexSpacer)}>{action}</div>
                            </div>
                            <div className={classes.searchContainer}>
                                <IndependentSearch
                                    className={classes.searchContainer}
                                    buttonClass={classNames(classes.searchButton, classes.buttonOverwrite)}
                                    buttonBaseClass={ButtonTypes.CUSTOM}
                                    isLarge={true}
                                    placeholder={t("Search Articles")}
                                    inputClass={classes.input}
                                    iconClass={classes.icon}
                                    buttonLoaderClassName={classes.buttonLoader}
                                    hideSearchButton={this.props.device === Devices.MOBILE}
                                    contentClass={classes.content}
                                    valueContainerClasses={classes.valueContainer}
                                />
                            </div>
                        </PanelWidgetHorizontalPadding>
                    </div>
                </Container>
            </div>
        );
    }
}

export default withDevice(Splash);

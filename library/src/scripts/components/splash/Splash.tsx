/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";
import Heading from "@library/components/Heading";
import Container from "@library/components/layouts/components/Container";
import { PanelWidget, PanelWidgetHorizontalPadding } from "@library/components/layouts/PanelLayout";
import { withDevice } from "@library/contexts/DeviceContext";
import { IDeviceProps, Devices } from "@library/components/DeviceChecker";
import IndependentSearch from "@library/components/IndependentSearch";
import { splashStyles, splashVariables } from "@library/styles/splashStyles";
import { buttonClasses, ButtonTypes } from "@library/styles/buttonStyles";
import { t } from "@library/application";

interface IProps extends IDeviceProps {
    title: string; // Often the message to display isn't the real H1
    className?: string;
}

/**
 * A component representing a single crumb in a breadcrumb component.
 */
export class Splash extends React.Component<IProps> {
    public render() {
        const classes = splashStyles();
        const buttons = buttonClasses();
        const { title, className } = this.props;
        return (
            <div className={classNames(className, classes.root)}>
                <div className={classes.outerBackground} />
                <Container>
                    <div className={classes.innerContainer}>
                        <PanelWidgetHorizontalPadding>
                            {title && <Heading title={title} className={classes.title} />}
                            <div className={classes.searchContainer}>
                                <IndependentSearch
                                    className={classes.searchContainer}
                                    buttonClass={classes.searchButton}
                                    buttonBaseClass={ButtonTypes.TRANSPARENT}
                                    isLarge={true}
                                    placeholder={t("Search Articles")}
                                    inputClass={classes.input}
                                    iconClass={classes.icon}
                                    buttonLoaderClassName={classes.buttonLoader}
                                    hideSearchButton={this.props.device === Devices.MOBILE}
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

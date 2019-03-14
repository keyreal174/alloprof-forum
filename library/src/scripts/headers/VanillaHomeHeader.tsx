/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import { Devices, IDeviceProps } from "../layout/DeviceChecker";
import { withDevice } from "@library/layout/DeviceContext";
import VanillaHeader from "VanillaHeader";

interface IProps extends IDeviceProps {}

/**
 * Implements Vanilla Header component. Note that this component uses a react portal.
 */
export class VanillaHomeHeader extends React.Component<IProps> {
    public render() {
        const isMobile = this.props.device === Devices.MOBILE;
        return isMobile ? <VanillaMobileHomeHeader /> : <VanillaHeader />;
    }
}

export default withDevice(VanillaHomeHeader);

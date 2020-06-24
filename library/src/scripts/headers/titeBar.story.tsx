/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { StoryHeading } from "@library/storybook/StoryHeading";
import { storiesOf } from "@storybook/react";
import React, { useState, createRef, useEffect } from "react";
import { MemoryRouter, Router } from "react-router";
import TitleBar, { TitleBar as TitleBarStatic } from "@library/headers/TitleBar";
import { Provider } from "react-redux";
import getStore from "@library/redux/getStore";
import { testStoreState } from "@library/__tests__/testStoreState";
import { LoadStatus } from "@library/@types/api/core";
import { IMe } from "@library/@types/api/users";
import { layoutVariables } from "@library/layout/panelLayoutStyles";
import { ButtonTypes } from "@library/forms/buttonTypes";
import Button from "@library/forms/Button";
import { DownTriangleIcon, GlobeIcon } from "@library/icons/common";
import { loadTranslations } from "@vanilla/i18n";
import { TitleBarDeviceProvider } from "@library/layout/TitleBarContext";
import { StoryFullPage } from "@library/storybook/StoryFullPage";

const localLogoUrl = require("./titleBarStoryLogo.png");

loadTranslations({});

const story = storiesOf("Headers", module);

const makeMockGuestUser: IMe = {
    name: "test",
    userID: 0,
    permissions: [],
    isAdmin: true,
    photoUrl: "",
    dateLastActive: "",
    countUnreadNotifications: 1,
};

story.add(
    "TitleBar Guest User",
    () => {
        const initialState = testStoreState({
            users: {
                current: {
                    status: LoadStatus.SUCCESS,
                    data: makeMockGuestUser,
                },
            },
            theme: {
                assets: {
                    data: {
                        logo: {
                            type: "image",
                            url: localLogoUrl,
                        },
                    },
                },
            },
            locales: {},
        });

        TitleBarStatic.registerBeforeMeBox(() => {
            return (
                <Button baseClass={ButtonTypes.TITLEBAR_LINK}>
                    <>
                        <GlobeIcon />
                        <DownTriangleIcon />
                    </>
                </Button>
            );
        });

        return (
            <MemoryRouter>
                <Provider store={getStore(initialState, true)}>
                    <TitleBarDeviceProvider>
                        <StoryFullPage>
                            <StoryHeading>Hamburger menu</StoryHeading>
                            <TitleBar useMobileBackButton={false} isFixed={false} />
                            <StoryHeading>Big Logo</StoryHeading>
                            <TitleBar useMobileBackButton={false} isFixed={false} />
                            <StoryHeading>Extra Navigation links</StoryHeading>
                            <TitleBar useMobileBackButton={false} isFixed={false} />
                        </StoryFullPage>
                    </TitleBarDeviceProvider>
                </Provider>
            </MemoryRouter>
        );
    },
    {
        chromatic: {
            viewports: [layoutVariables().panelLayoutBreakPoints.noBleed, layoutVariables().panelLayoutBreakPoints.xs],
        },
    },
);

/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { MemoryRouter } from "react-router";
import SearchContext from "@library/contexts/SearchContext";
import { StoryContent } from "@library/storybook/StoryContent";
import { StoryHeading } from "@library/storybook/StoryHeading";
import { MockSearchData } from "@library/contexts/DummySearchContext";
import { storyWithConfig } from "@library/storybook/StoryContext";
import { color } from "csx";
import Banner from "@library/banner/Banner";
import { SearchBarButtonType } from "@library/headers/mebox/pieces/compactSearchStyles";
import { DeviceProvider } from "@library/layout/DeviceContext";
import { BannerAlignment, SearchBarPresets } from "@library/banner/bannerStyles";
import { globalVariables } from "@library/styles/globalStyleVars";
import { layoutVariables } from "@library/layout/panelLayoutStyles";

export default {
    title: "Banner",
    parameters: {
        chromatic: {
            viewports: [1400, 400],
        },
    },
};

function StoryBanner(props: { title: string }) {
    return (
        <MemoryRouter>
            <SearchContext.Provider value={{ searchOptionProvider: new MockSearchData() }}>
                <DeviceProvider>
                    <Banner
                        title={props.title}
                        description="This is a description. They're pretty great, you should try one sometime."
                    />
                </DeviceProvider>
            </SearchContext.Provider>
        </MemoryRouter>
    );
}

export const SearchStyleDefault = storyWithConfig(
    {
        useWrappers: false,
    },
    () => <StoryBanner title="Search Styles - No Border (default) with search" />,
);

export const BorderedSearchWithSolidButton = storyWithConfig(
    {
        useWrappers: false,
        themeVars: {
            global: {
                mainColors: {
                    primary: color("#111111"),
                },
                body: {
                    backgroundImage: {
                        color: color("#efefef"),
                    },
                },
                content: {
                    width: 1350,
                },
            },
            banner: {
                options: {
                    alignment: BannerAlignment.LEFT,
                },
                colors: {
                    bg: "#fff",
                    primaryContrast: "#111111",
                },
                outerBackground: {
                    color: "#FFF6F5",
                    image: "linear-gradient(215.7deg, #FFFDFC 16.08%, #FFF6F5 63.71%), #C4C4C4",
                },
                description: {
                    font: {
                        color: "#323232",
                    },
                },
                imageElement: {
                    image:
                        "https://user-images.githubusercontent.com/1770056/73629535-7fc98600-4621-11ea-8f0b-06b21dbd59e3.png",
                },
                searchBar: {
                    preset: SearchBarPresets.BORDER,
                },
                searchButtonOptions: {
                    type: SearchBarButtonType.SOLID,
                },
                spacing: {
                    padding: {
                        top: 87,
                        bottom: 87,
                    },
                },
            },
        },
    },
    () => <StoryBanner title="Bordered with button solid" />,
);

export const BorderedSearchWithTransparentButton = storyWithConfig(
    {
        useWrappers: false,
        themeVars: {
            global: {
                mainColors: {
                    primary: color("#111111"),
                },
                body: {
                    backgroundImage: {
                        color: color("#efefef"),
                    },
                },
                content: {
                    width: 1350,
                },
            },
            banner: {
                options: {
                    alignment: BannerAlignment.LEFT,
                },
                colors: {
                    bg: "#fff",
                    primaryContrast: "#111111",
                },
                outerBackground: {
                    color: "#FFF6F5",
                    image: "linear-gradient(215.7deg, #FFFDFC 16.08%, #FFF6F5 63.71%), #C4C4C4",
                },
                description: {
                    font: {
                        color: "#323232",
                    },
                },
                imageElement: {
                    image:
                        "https://user-images.githubusercontent.com/1770056/73629535-7fc98600-4621-11ea-8f0b-06b21dbd59e3.png",
                },
                searchBar: {
                    preset: SearchBarPresets.BORDER,
                },
                searchButtonOptions: {
                    type: SearchBarButtonType.TRANSPARENT,
                    font: {
                        color: "#323232",
                    },
                },
                spacing: {
                    padding: {
                        top: 87,
                        bottom: 87,
                    },
                },
            },
        },
    },
    () => <StoryBanner title="Bordered with button transparent" />,
);

export const NoDescription = storyWithConfig(
    {
        useWrappers: false,
        themeVars: {
            banner: {
                options: {
                    hideDesciption: true,
                },
                colors: {
                    primary: color("#9279a8"),
                },
            },
        },
    },
    () => <StoryBanner title="No Description" />,
);

export const NoSearch = storyWithConfig(
    {
        useWrappers: false,
        themeVars: {
            banner: {
                options: {
                    hideSearch: true,
                },
            },
        },
    },
    () => <StoryBanner title="No Search" />,
);

export const NoBackgroundSolidButton = storyWithConfig(
    {
        useWrappers: false,
        themeVars: {
            banner: {
                colors: {
                    primary: color("#9279a8"),
                    primaryContrast: color("#fff"),
                    bg: color("#699dff"),
                    fg: color("rgb(255,254,250)"),
                },
                backgrounds: {
                    useOverlay: false,
                },
                searchButtonOptions: {
                    type: SearchBarButtonType.SOLID,
                },
                outerBackground: {
                    image: "none",
                },
            },
        },
    },
    () => <StoryBanner title="No Background - Solid button" />,
);

// export const LeftAligned = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 options: {
//                     alignment: BannerAlignment.LEFT,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Left Aligned" />,
// );
//
// export const BackgroundImage = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 outerBackground: {
//                     image: "https://us.v-cdn.net/5022541/uploads/726/MNT0DAGT2S4K.jpg",
//                 },
//                 backgrounds: {
//                     useOverlay: true,
//                 },
//                 searchButtonOptions: {
//                     type: SearchBarButtonType.TRANSPARENT,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="With a background image" />,
// );
//
// export const CustomOverlay = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 outerBackground: {
//                     image: "https://us.v-cdn.net/5022541/uploads/726/MNT0DAGT2S4K.jpg",
//                 },
//                 backgrounds: {
//                     useOverlay: true,
//                     overlayColor: color("rgba(100, 44, 120, 0.5)"),
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="With a background image (and colored overlay)" />,
// );
//
// export const ImageAsElement = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             global: {
//                 mainColors: {
//                     primary: color("#111111"),
//                 },
//                 body: {
//                     backgroundImage: {
//                         color: color("#efefef"),
//                     },
//                 },
//             },
//             banner: {
//                 options: {
//                     alignment: BannerAlignment.LEFT,
//                 },
//                 colors: {
//                     bg: "#fff",
//                     primaryContrast: "#111111",
//                 },
//                 outerBackground: {
//                     color: "#FFF6F5",
//                     image: "linear-gradient(215.7deg, #FFFDFC 16.08%, #FFF6F5 63.71%), #C4C4C4",
//                 },
//                 description: {
//                     font: {
//                         color: "#323232",
//                     },
//                 },
//                 imageElement: {
//                     image:
//                         "https://user-images.githubusercontent.com/1770056/73629535-7fc98600-4621-11ea-8f0b-06b21dbd59e3.png",
//                 },
//                 searchButtonOptions: {
//                     type: SearchBarButtonType.NONE,
//                 },
//                 spacing: {
//                     padding: {
//                         top: 87,
//                         bottom: 87,
//                     },
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Image as Element - (With Left Alignment)" />,
// );

// (ImageAsElement as any).story = {
//     parameters: {
//         chromatic: {
//             viewports: [1400, globalVariables().content.width, layoutVariables().panelLayoutBreakPoints.oneColumn, 400],
//         },
//     },
// };

// export const ImageAsElementWide = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             global: {
//                 mainColors: {
//                     primary: color("#111111"),
//                 },
//                 body: {
//                     backgroundImage: {
//                         color: color("#efefef"),
//                     },
//                 },
//                 content: {
//                     width: 1350,
//                 },
//             },
//             banner: {
//                 options: {
//                     alignment: BannerAlignment.LEFT,
//                 },
//                 colors: {
//                     bg: "#fff",
//                     primaryContrast: "#111111",
//                 },
//                 outerBackground: {
//                     color: "#FFF6F5",
//                     image: "linear-gradient(215.7deg, #FFFDFC 16.08%, #FFF6F5 63.71%), #C4C4C4",
//                 },
//                 description: {
//                     font: {
//                         color: "#323232",
//                     },
//                 },
//                 imageElement: {
//                     image:
//                         "https://user-images.githubusercontent.com/1770056/73629535-7fc98600-4621-11ea-8f0b-06b21dbd59e3.png",
//                 },
//                 searchButtonOptions: {
//                     type: SearchBarButtonType.NONE,
//                 },
//                 spacing: {
//                     padding: {
//                         top: 87,
//                         bottom: 87,
//                     },
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Image as Element - (With Left Alignment)" />,
// );

// export const SearchStyleNoBorderNoButton = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 searchButtonOptions: {
//                     type: SearchBarButtonType.NONE,
//                 },
//                 searchBar: {
//                     preset: SearchBarPresets.NO_BORDER,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Search Styles - No Border no button" />,
// );
//
// export const SearchStyleBordered = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 searchBar: {
//                     preset: SearchBarPresets.BORDER,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Search Styles - Bordered" />,
// );
//
// export const SearchStylesBorderedNoButton = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 searchBar: {
//                     preset: SearchBarPresets.BORDER,
//                 },
//                 searchButtonOptions: {
//                     type: SearchBarButtonType.NONE,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Search Styles - Bordered, no button" />,
// );
//
// export const SearchStyleShadowed = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 searchBar: {
//                     showShadow: true,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Search Styles - Shadowed" />,
// );
//
// export const SearchStylesShadowedNoButton = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 searchBar: {
//                     showShadow: true,
//                 },
//                 searchButtonOptions: {
//                     type: SearchBarButtonType.NONE,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Search Styles - Shadowed no button" />,
// );
//
// export const SearchStylesUnitedBorder = storyWithConfig(
//     {
//         useWrappers: false,
//         themeVars: {
//             banner: {
//                 searchBar: {
//                     preset: SearchBarPresets.UNIFIED_BORDER,
//                 },
//             },
//         },
//     },
//     () => <StoryBanner title="Search Styles - Unified border" />,
// );

// (ImageAsElementWide as any).story = {
//     parameters: {
//         chromatic: {
//             viewports: [1450, 1350, layoutVariables().panelLayoutBreakPoints.oneColumn, 400],
//         },
//     },
// };

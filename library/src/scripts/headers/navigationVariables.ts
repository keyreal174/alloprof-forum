/*
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
import { getMeta, t, assetUrl, formatUrl } from "@library/utility/appUtils";
import { variableFactory, useThemeCache } from "@library/styles/styleUtils";
import { getCurrentLocale } from "@vanilla/i18n";
import { ITitleBarNav } from "./mebox/pieces/TitleBarNavItem";

type INavItemGenerator = () => ITitleBarNav;

const navItemGenerators: INavItemGenerator[] = [];

export function registerDefaultNavItem(navItemGetter: INavItemGenerator) {
    navItemGenerators.push(navItemGetter);
}

export const navigationVariables = useThemeCache(() => {
    const makeVars = variableFactory("navigation");
    const forumEnabled = getMeta("siteSection.apps.forum", true);

    let defaultForumLinks: ITitleBarNav[] = [];
    if (forumEnabled) {
        defaultForumLinks = [
            {
                to: "/discussions",
                children: t("Discussions"),
            },
            { to: "/categories", children: t("Categories") },
        ];
    }
    const navItems: { [language: string]: ITitleBarNav[] | undefined; default: ITitleBarNav[] } = makeVars("navItems", {
        default: [...defaultForumLinks, ...navItemGenerators.map(generator => generator())],
        [getCurrentLocale()]: undefined,
    });

    const logo = makeVars("logo", {
        url: "/",
    });

    const currentLocale = getCurrentLocale();

    const getNavItemsForLocale = (locale = currentLocale): ITitleBarNav[] => {
        if (locale in navItems) {
            return navItems[locale] ?? navItems.default;
        } else {
            return navItems.default;
        }
    };

    return { navItems, logo, getNavItemsForLocale };
});

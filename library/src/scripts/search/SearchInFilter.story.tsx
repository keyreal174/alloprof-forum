/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import { useState } from "react";
import { storyWithConfig } from "@library/storybook/StoryContext";
import {
    SearchFilterAll,
    TypeArticles,
    TypeCategoriesAndGroups,
    TypeDiscussions,
    TypeMember,
} from "@library/icons/searchIcons";
import { t } from "@vanilla/i18n/src";
import { StoryParagraph } from "@library/storybook/StoryParagraph";
import { layoutVariables } from "@library/layout/panelLayoutStyles";
import { ISearchInButton, SearchInFilter } from "@library/search/SearchInFilter";
import { SearchFilterContextProvider } from "@library/contexts/SearchFilterContext";

interface IProps {
    activeItem?: string;
    filters?: ISearchInButton[];
    endFilters?: ISearchInButton[]; // At the end, separated by vertical line
    message?: string;
}

const dummmyFilters: ISearchInButton[] = [
    {
        label: t("All Content"),
        icon: <SearchFilterAll />,
        data: "all",
    },
    {
        label: t("Discussions"),
        icon: <TypeDiscussions />,
        data: "discussions",
    },
    {
        label: t("Articles"),
        icon: <TypeArticles />,
        data: "articles",
    },
    {
        label: t("Categories & Groups"),
        icon: <TypeCategoriesAndGroups />,
        data: "categoriesAndGroups",
    },
];

const dummmyEndFilters: ISearchInButton[] = [
    {
        label: t("Members"),
        icon: <TypeMember />,
        data: "members",
    },
];

export default {
    title: "Search/Filters",
    parameters: {
        chromatic: {
            viewports: layoutVariables().panelLayoutBreakPoints.twoColumn,
        },
    },
};

/**
 * Implements the search bar component
 */
export function SearchFilter(props: IProps) {
    const { activeItem = "all", filters = dummmyFilters, endFilters = dummmyEndFilters, message } = props;
    const [data, setData] = useState(activeItem);

    return (
        <>
            {message && <StoryParagraph>{message}</StoryParagraph>}
            <SearchInFilter filters={filters} endFilters={endFilters} setData={setData} activeItem={data} />
        </>
    );
}

export const NoMembers = storyWithConfig({}, () => <SearchFilter activeItem={"Groups"} endFilters={[]} />);
export const NotRendered = storyWithConfig({}, () => (
    <SearchFilter
        message={
            "This page should stay empty, we don't want to render the component unless there are more than 1 filters."
        }
        filters={[]}
        endFilters={[]}
    />
));

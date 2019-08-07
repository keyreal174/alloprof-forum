/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { StoryHeading } from "@library/storybook/StoryHeading";
import { storiesOf } from "@storybook/react";
import React from "react";
import { StoryContent } from "@library/storybook/StoryContent";
import { ButtonTypes } from "@library/forms/buttonStyles";
import { t } from "@library/utility/appUtils";
import IndependentSearch from "@library/features/search/IndependentSearch";
import { splashClasses } from "@library/splash/splashStyles";
import SearchContext from "@library/contexts/SearchContext";
import { MockSearchData } from "@library/contexts/DummySearchContext";
import { MemoryRouter } from "react-router";
import ResultList from "@library/result/ResultList";
import { ResultMeta } from "@library/result/ResultMeta";
import { ArticleMeta } from "@knowledge/modules/article/components/ArticleMeta";
import { PublishStatus } from "@library/@types/api/core";
import { IUserFragment } from "@library/@types/api/users";
import { ICrumbString } from "@library/navigation/BreadCrumbString";
import { AttachmentType } from "@library/content/attachments/AttatchmentType";
import DraftsList from "@knowledge/modules/editor/components/DraftsList";
import DraftsListItem from "@knowledge/modules/editor/components/DraftsListItem";
import { string } from "prop-types";
import { DraftPreview } from "@knowledge/modules/drafts/components/DraftPreview";
import DraftList from "@knowledge/modules/drafts/components/DraftList";
import { KbRecordType } from "@knowledge/navigation/state/NavigationModel";
import classNames from "classnames";
import DropDownItemLink from "@library/flyouts/items/DropDownItemLink";
import DropDownItemButton from "@library/flyouts/items/DropDownItemButton";
import DropDown from "@library/flyouts/DropDown";
import { StoryExampleDropDownDraft } from "./StoryExampleDropDownDraft";

const story = storiesOf("Search", module);

story.add("Search Components", () => {
    const dummyUserFragment = {
        userID: 1,
        name: "Joe",
        photoUrl: "",
        dateLastActive: "2016-07-25 17:51:15",
    };

    return (
        <StoryContent>
            <StoryHeading depth={1}>Search Elements</StoryHeading>

            <StoryHeading>Search Box</StoryHeading>
            <SearchContext.Provider value={{ searchOptionProvider: new MockSearchData() }}>
                <MemoryRouter>
                    <div className={splashClasses().searchContainer}>
                        <IndependentSearch
                            buttonClass={splashClasses().searchButton}
                            buttonBaseClass={ButtonTypes.CUSTOM}
                            isLarge={true}
                            placeholder={t("Search")}
                            inputClass={splashClasses().input}
                            iconClass={splashClasses().icon}
                            buttonLoaderClassName={splashClasses().buttonLoader}
                            contentClass={splashClasses().content}
                            valueContainerClasses={splashClasses().valueContainer}
                        />
                    </div>
                </MemoryRouter>
            </SearchContext.Provider>

            <StoryHeading>Search Results</StoryHeading>
            <ResultList
                results={[
                    {
                        name: "Example search result",
                        headingLevel: 3,
                        url: "#",
                        excerpt:
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake.",
                        meta: (
                            <ResultMeta
                                dateUpdated={"2016-07-25 17:51:15"}
                                updateUser={dummyUserFragment}
                                crumbs={[{ name: "This" }, { name: "is" }, { name: "the" }, { name: "breadcrumb" }]}
                                status={PublishStatus.PUBLISHED}
                                type={"Article"}
                            />
                        ),
                        attachments: [{ name: "My File", type: AttachmentType.WORD }],
                    },
                    {
                        name: "Example search result",
                        headingLevel: 3,
                        url: "#",
                        image: "https://upload.wikimedia.org/wikipedia/en/7/70/Bob_at_Easel.jpg",
                        excerpt:
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake.",
                        meta: (
                            <ResultMeta
                                dateUpdated={"2016-07-25 17:51:15"}
                                updateUser={dummyUserFragment}
                                crumbs={[{ name: "This" }, { name: "is" }, { name: "the" }, { name: "breadcrumb" }]}
                                status={PublishStatus.PUBLISHED}
                                type={"Article"}
                            />
                        ),
                    },
                    {
                        name: "Example search result",
                        headingLevel: 3,
                        url: "#",
                        excerpt:
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake.",
                    },
                    {
                        name: "Example search result",
                        headingLevel: 3,
                        url: "#",
                        meta: (
                            <ResultMeta
                                dateUpdated={"2016-07-25 17:51:15"}
                                updateUser={dummyUserFragment}
                                crumbs={[{ name: "This" }, { name: "is" }, { name: "the" }, { name: "breadcrumb" }]}
                                status={PublishStatus.PUBLISHED}
                                type={"Article"}
                            />
                        ),
                    },
                    {
                        name: "Example search result",
                        headingLevel: 3,
                        url: "#",
                        excerpt:
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake.",
                        meta: (
                            <ResultMeta
                                dateUpdated={"2016-07-25 17:51:15"}
                                updateUser={dummyUserFragment}
                                crumbs={[{ name: "This" }, { name: "is" }, { name: "the" }, { name: "breadcrumb" }]}
                                status={PublishStatus.PUBLISHED}
                                type={"Article"}
                            />
                        ),
                    },
                ]}
            />
            <StoryHeading>Category result (used on categories page)</StoryHeading>
            <ResultList
                results={[
                    {
                        name: "Example category result",
                        headingLevel: 3,
                        url: "#",
                        meta: <ResultMeta dateUpdated={"2016-07-25 17:51:15"} updateUser={dummyUserFragment} />,
                    },
                ]}
            />
            <StoryHeading>Draft result (used on categories page)</StoryHeading>
            <MemoryRouter>
                <DraftsList hideTitle={true}>
                    <DraftPreview
                        dateUpdated={"2016-07-25 17:51:15"}
                        updateUserID={1}
                        insertUserID={1}
                        body={
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake."
                        }
                        headingLevel={3}
                        draftID={1}
                        recordType={"article"}
                        excerpt={
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake."
                        }
                        attributes={{
                            name: "Draft example",
                        }}
                        menuOverwrite={<StoryExampleDropDownDraft />}
                    />
                    <DraftPreview
                        dateUpdated={"2016-07-25 17:51:15"}
                        updateUserID={1}
                        insertUserID={1}
                        body={
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake."
                        }
                        headingLevel={3}
                        draftID={1}
                        recordType={"article"}
                        excerpt={""}
                        attributes={{
                            name: "",
                        }}
                        menuOverwrite={<StoryExampleDropDownDraft />}
                    />
                    <DraftPreview
                        dateUpdated={"2016-07-25 17:51:15"}
                        updateUserID={1}
                        insertUserID={1}
                        body={
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake."
                        }
                        headingLevel={3}
                        draftID={1}
                        recordType={"article"}
                        excerpt={""}
                        attributes={{
                            name: "Draft example",
                        }}
                        menuOverwrite={<StoryExampleDropDownDraft />}
                    />
                    <DraftPreview
                        dateUpdated={"2016-07-25 17:51:15"}
                        updateUserID={1}
                        insertUserID={1}
                        body={
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake."
                        }
                        headingLevel={3}
                        draftID={1}
                        recordType={"article"}
                        excerpt={
                            "Donut danish halvah macaroon chocolate topping. Sugar plum cookie chupa chups tootsie roll tiramisu cupcake carrot cake. Ice cream biscuit sesame snaps fruitcake."
                        }
                        attributes={{
                            name: "",
                        }}
                        menuOverwrite={<StoryExampleDropDownDraft />}
                    />
                </DraftsList>
            </MemoryRouter>
        </StoryContent>
    );
});

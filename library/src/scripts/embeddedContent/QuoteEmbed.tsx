/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React, { useState, useCallback, useMemo, useEffect } from "react";
import { IBaseEmbedProps } from "@library/embeddedContent/embedService";
import { IUser, IUserFragment } from "@library/@types/api/users";
import { useUniqueID } from "@library/utility/idUtils";
import classnames from "classnames";
import { makeProfileUrl, t } from "@library/utility/appUtils";
import SmartLink from "@library/routing/links/SmartLink";
import DateTime from "@library/content/DateTime";
import { CollapsableContent } from "@library/content/CollapsableContent";
import { EmbedContainer } from "@library/embeddedContent/EmbedContainer";
import { EmbedContent } from "@library/embeddedContent/EmbedContent";
import { BottomChevronIcon, DiscussionIcon, RightChevronIcon, TopChevronIcon } from "@library/icons/common";
import UserContent from "@library/content/UserContent";
import { quoteEmbedClasses } from "@library/embeddedContent/quoteEmbedStyles";
import { metasClasses } from "@library/styles/metasStyles";
import classNames from "classnames";
import { ICategory } from "@vanilla/addon-vanilla/@types/api/categories";
import ScreenReaderContent from "@library/layout/ScreenReaderContent";

interface IProps extends IBaseEmbedProps {
    body: string;
    dateInserted: string;
    insertUser: IUserFragment | IUser;
    expandByDefault?: boolean;
    discussionLink?: string;
    postLink?: string; // should always be there for citation reference
    category?: ICategory;
    // For compatibility, new options are hidden by default
    displayOptions?: {
        showUserLabel?: boolean;
        showDiscussionLink?: boolean;
        showPostLink?: boolean;
        showCategoryLink?: boolean;
    };
}

/**
 * An embed class for quoted user content on the same site.
 *
 * This is not an editable quote. Instead it an expandable/collapsable snapshot of the quoted/embedded comment/discussion.
 */
export function QuoteEmbed(props: IProps) {
    const {
        body,
        insertUser,
        name,
        url,
        dateInserted,
        discussionLink,
        postLink,
        category,
        displayOptions = {},
    } = props;

    const classes = quoteEmbedClasses();
    const userUrl = makeProfileUrl(insertUser.name);
    const classesMeta = metasClasses();
    const {
        showUserLabel = false,
        showDiscussionLink = false,
        showPostLink = false,
        showCategoryLink = false,
    } = displayOptions;

    const linkToDiscussion = showDiscussionLink && discussionLink && (
        <SmartLink title={t("View Original Discussion")} to={discussionLink}>
            <DiscussionIcon />
            <ScreenReaderContent>{t("View Original Discussion")}</ScreenReaderContent>
        </SmartLink>
    );

    const linkToPost = showPostLink && postLink && (
        <SmartLink to={postLink}>
            {t("View Post")}
            <RightChevronIcon />
        </SmartLink>
    );

    return (
        <EmbedContainer withPadding={false} className={classes.root}>
            <EmbedContent type="Quote" inEditor={props.inEditor}>
                <article className={classes.body}>
                    <header className={classes.header}>
                        <SmartLink to={userUrl} className={classNames(classesMeta.meta, classes.userName)}>
                            <span className="embedQuote-userName">{insertUser.name}</span>
                        </SmartLink>

                        <div className={classesMeta.root}>
                            <SmartLink to={url} className={classNames(classesMeta.meta)}>
                                <DateTime timestamp={dateInserted} />
                            </SmartLink>
                            {category && showCategoryLink && (
                                <SmartLink to={category.url} className={classNames(classesMeta.meta)}>
                                    {category.name}
                                </SmartLink>
                            )}
                        </div>

                        {name && (
                            <h2 className={classes.title}>
                                <SmartLink to={url} className={classes.titleLink}>
                                    {name}
                                </SmartLink>
                            </h2>
                        )}
                    </header>
                    <CollapsableContent
                        className={classes.content}
                        maxHeight={200}
                        isExpandedDefault={!!props.expandByDefault}
                    >
                        <blockquote cite={postLink}>
                            <UserContent content={body} />
                        </blockquote>
                    </CollapsableContent>
                    {(linkToPost || (showDiscussionLink && discussionLink)) && (
                        <footer className={classes.footer}>{linkToPost}</footer>
                    )}
                </article>
            </EmbedContent>
        </EmbedContainer>
    );
}

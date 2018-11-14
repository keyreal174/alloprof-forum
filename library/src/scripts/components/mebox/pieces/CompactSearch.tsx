/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import SearchBar from "@library/components/forms/select/SearchBar";
import { t } from "@library/application";
import qs from "qs";
import apiv2 from "@library/apiv2";
import Button, { ButtonBaseClass } from "@library/components/forms/Button";
import classNames from "classnames";
import { search } from "@library/components/icons/header";
import { uniqueIDFromPrefix } from "@library/componentIDs";
import SearchOption from "@library/components/search/SearchOption";

export interface ICompactSearchProps {
    className?: string;
    placeholder?: string;
    open: boolean;
    onOpenSearch: () => void;
    onCloseSearch: () => void;
    cancelButtonClassName?: string;
}

/**
 * Implements Compact Search component for header
 */
export default class CompactSearch extends React.Component<ICompactSearchProps> {
    private id = uniqueIDFromPrefix("compactSearch");

    public render() {
        return (
            <div className="compactSearch">
                {!this.props.open && (
                    <Button
                        onClick={this.props.onOpenSearch}
                        className={classNames("compactSearch-open", "meBox-button")}
                        title={t("Search")}
                        aria-expanded={false}
                        aria-haspopup="true"
                        baseClass={ButtonBaseClass.CUSTOM}
                        aria-controls={this.id}
                    >
                        <div className="meBox-buttonContent">{search()}</div>
                    </Button>
                )}
                {this.props.open && (
                    <div className={classNames("compactSearch-contents")}>
                        <SearchBar
                            id={this.id}
                            placeholder={this.props.placeholder}
                            onChange={this.onSearch}
                            loadOptions={this.loadOptions}
                            value={""}
                            onSearch={this.onSearch}
                            optionComponent={SearchOption}
                            noHeading={true}
                            title={t("Search")}
                            disabled={!this.props.open}
                            hideSearchButton={true}
                        />
                        <Button
                            onClick={this.props.onCloseSearch}
                            className={classNames("compactSearch-close", this.props.cancelButtonClassName)}
                            title={t("Search")}
                            aria-expanded={true}
                            aria-haspopup="true"
                            aria-controls={this.id}
                            baseClass={ButtonBaseClass.CUSTOM}
                        >
                            {t("Cancel")}
                        </Button>
                    </div>
                )}
            </div>
        );
    }

    public onSearch = () => {
        // Do nothing;
    };

    /**
     * Simple data loading function for the search bar/react-select.
     */
    private loadOptions = async (value: string) => {
        const queryObj = {
            name: value,
            expand: ["user", "category"],
        };
        const query = qs.stringify(queryObj);
        const response = await apiv2.get(`/knowledge/search?${query}`);
        return response.data.map(result => {
            return {
                label: result.name,
                value: result.name,
                data: result,
            };
        });
    };
}

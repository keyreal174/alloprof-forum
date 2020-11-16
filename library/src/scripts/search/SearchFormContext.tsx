/**
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import apiv2 from "@library/apiv2";
import { TypeAllIcon } from "@library/icons/searchIcons";
import SimplePagerModel from "@library/navigation/SimplePagerModel";
import { FilterPanelAll } from "@library/search/panels/FilterPanelAll";
import { SearchActions } from "@library/search/SearchActions";
import { DEFAULT_CORE_SEARCH_FORM, INITIAL_SEARCH_STATE, searchReducer } from "@library/search/searchReducer";
import { ISearchForm, ISearchRequestQuery, ISearchResults, ISearchFormBase } from "@library/search/searchTypes";
import {
    ALLOWED_GLOBAL_SEARCH_FIELDS,
    ALL_CONTENT_DOMAIN_NAME,
    MEMBERS_RECORD_TYPE,
    MEMBERS_DOMAIN_NAME,
    PLACES_DOMAIN_NAME,
} from "@library/search/searchConstants";
import { t } from "@vanilla/i18n";
import { ILoadable } from "@vanilla/library/src/scripts/@types/api/core";
import React, { useCallback, useContext, useReducer } from "react";
import merge from "lodash/merge";
import Result from "@library/result/Result";
import { ISelectBoxItem } from "@library/forms/select/SelectBox";
import { useSearchScope } from "@library/features/search/SearchScopeContext";
import { getCurrentLocale } from "@vanilla/i18n";

interface ISearchContextValue<ExtraFormValues extends object = ISearchFormBase> {
    results: ILoadable<ISearchResults>;

    form: ISearchForm<ExtraFormValues>;

    /**
     * Get all of the DOM elements for selecting query values for a search domain.
     * @param domain The search domain to get values for.
     */
    getFilterComponentsForDomain(domain: string): React.ReactNode;

    /**
     * Update some form values.
     */
    updateForm(updateValues: Partial<ISearchForm<ExtraFormValues>>): void;

    /**
     * Update some form values.
     */
    resetForm(): void;

    /**
     * Perform a search.
     */
    search(): void;

    /**
     * Get all of the regitered search domains.
     */
    getDomains(): ISearchDomain[];

    /**
     * Get the current search domain of the form.
     */
    getCurrentDomain(): ISearchDomain;

    /**
     * Get the default values for the form.
     */
    getDefaultFormValues(): ISearchForm;
}

const SearchContext = React.createContext<ISearchContextValue>({
    getFilterComponentsForDomain: () => null,
    updateForm: () => {},
    resetForm: () => {},
    results: INITIAL_SEARCH_STATE.results,
    form: DEFAULT_CORE_SEARCH_FORM,
    search: () => {},
    getDomains: () => {
        return [];
    },
    getCurrentDomain: () => {
        throw new Error("Context implementation is required for this method");
    },
    getDefaultFormValues: () => {
        return DEFAULT_CORE_SEARCH_FORM;
    },
});

interface IProps {
    children?: React.ReactNode;
}

export const SEARCH_LIMIT_DEFAULT = 10;

export function getGlobalSearchSorts(): ISelectBoxItem[] {
    return [
        {
            content: t("Best Match"),
            value: "relevance",
        },
        {
            content: t("Newest"),
            value: "-dateInserted",
        },
        {
            content: t("Oldest"),
            value: "dateInserted",
        },
    ];
}

export function SearchFormContextProvider(props: IProps) {
    const [state, dispatch] = useReducer(searchReducer, INITIAL_SEARCH_STATE);

    const getFilterComponentsForDomain = (domain: string) => {
        return SearchFormContextProvider.extraFilters.map((extraFilter, i) => {
            if (extraFilter.searchDomain === domain) {
                return <React.Fragment key={i}>{extraFilter.filterNode}</React.Fragment>;
            } else {
                return null;
            }
        });
    };

    const ALL_CONTENT_DOMAIN: ISearchDomain = {
        key: ALL_CONTENT_DOMAIN_NAME,
        name: t("All Content"),
        sort: 0,
        icon: <TypeAllIcon />,
        PanelComponent: FilterPanelAll,
        getAllowedFields: () => {
            return ALLOWED_GLOBAL_SEARCH_FIELDS;
        },
        getRecordTypes: () => {
            // Gather all other domains, and return their types.
            const allTypes: string[] = [];
            for (const pluggableDomain of SearchFormContextProvider.pluggableDomains) {
                allTypes.push(...pluggableDomain.getRecordTypes());
            }

            return allTypes.filter((t) => t !== MEMBERS_RECORD_TYPE);
        },
        getSortValues: getGlobalSearchSorts,
        transformFormToQuery: (form: ISearchForm) => {
            const query: ISearchRequestQuery = { ...form };
            if (query.sort === "relevance") {
                delete query.sort;
            }
            return query;
        },
        getDefaultFormValues: () => {
            return {
                sort: "relevance",
            };
        },
        isIsolatedType: () => false,
        ResultComponent: Result,
    };

    const getDomains = () => {
        return [ALL_CONTENT_DOMAIN, ...SearchFormContextProvider.pluggableDomains];
    };

    const getCurrentDomain = (): ISearchDomain => {
        return (
            getDomains().find((pluggableDomain) => {
                return pluggableDomain.key === state.form.domain;
            }) ?? ALL_CONTENT_DOMAIN
        );
    };

    const getDate = (form: ISearchFormBase): string | undefined => {
        let dateInserted: string | undefined;
        if (form.startDate && form.endDate) {
            if (form.startDate === form.endDate) {
                // Simple equality.
                dateInserted = form.startDate;
            } else {
                // Date range
                dateInserted = `[${form.startDate},${form.endDate}]`;
            }
        } else if (form.startDate) {
            // Only start date
            dateInserted = `>=${form.startDate}`;
        } else if (form.endDate) {
            // Only end date.
            dateInserted = `<=${form.endDate}`;
        }
        return dateInserted;
    };

    const makeFilterForm = (form: ISearchFormBase): Partial<ISearchForm> => {
        const currentDomain = getCurrentDomain();
        const filterForm: Partial<ISearchForm> = {};
        const allowedFields = [...ALL_CONTENT_DOMAIN.getAllowedFields(), ...currentDomain.getAllowedFields()];
        for (const [key, value] of Object.entries(form)) {
            if (allowedFields.includes(key)) {
                filterForm[key] = value;
            }
        }
        return filterForm;
    };

    const searchScope = useSearchScope();
    const buildQuery = (form: ISearchForm): ISearchRequestQuery => {
        const filterForm = makeFilterForm(form);
        const currentDomain = getCurrentDomain();

        const allowedSorts = currentDomain.getSortValues().map((val) => val.value);
        const sort = allowedSorts.includes(form.sort) ? form.sort : undefined;

        const commonQueryEntries: ISearchRequestQuery = {
            page: form.page,
            limit: SEARCH_LIMIT_DEFAULT,
            dateInserted: getDate(form),
            locale: getCurrentLocale(),
            collapse: true,
            ...currentDomain.transformFormToQuery(filterForm),
            sort,
        };
        if (searchScope.value?.value) {
            commonQueryEntries.scope = searchScope.value.value;
        }

        let finalQuery: ISearchRequestQuery;

        if (currentDomain.key === MEMBERS_DOMAIN_NAME) {
            finalQuery = {
                ...commonQueryEntries,
                scope: "site", // Force site domain for members.
                recordTypes: [MEMBERS_RECORD_TYPE],
                expand: [],
            };
        } else if (currentDomain.key === PLACES_DOMAIN_NAME) {
            // No recordTypes, only types (from form)
            finalQuery = {
                ...commonQueryEntries,
                expand: ["breadcrumbs", "image", "excerpt", "-body"],
            };
        } else if (currentDomain.hasSpecificRecord?.(form)) {
            finalQuery = {
                ...commonQueryEntries,
                expand: ["insertUser", "breadcrumbs", "image", "excerpt", "-body"],
            };
        } else {
            finalQuery = {
                domain: form.domain,
                ...commonQueryEntries,
                insertUserIDs:
                    form.authors && form.authors.length
                        ? form.authors.map((author) => author.value as number)
                        : undefined,
                recordTypes: currentDomain.getRecordTypes(),
                expand: ["insertUser", "breadcrumbs", "image", "excerpt", "-body"],
            };
        }

        // Filter out empty fields.
        Object.entries(finalQuery).forEach(([field, value]) => {
            if (value === "") {
                delete finalQuery[field];
            }
        });

        return finalQuery;
    };

    const search = async () => {
        const { form } = state;

        dispatch(SearchActions.performSearchACs.started(form));

        try {
            const query = buildQuery(form);
            const response = await apiv2.get("/search", {
                params: query,
            });
            dispatch(
                SearchActions.performSearchACs.done({
                    params: form,
                    result: {
                        results: response.data.map((item) => {
                            item.body = item.excerpt ?? item.body;
                            return item;
                        }),
                        pagination: SimplePagerModel.parseHeaders(response.headers),
                    },
                }),
            );
        } catch (error) {
            dispatch(SearchActions.performSearchACs.failed({ params: form, error }));
        }
    };

    const updateForm = useCallback((update: Partial<ISearchForm>) => {
        dispatch(SearchActions.updateSearchFormAC(update));
    }, []);

    const resetForm = useCallback(() => {
        dispatch(SearchActions.resetFormAC());
    }, []);

    const getDefaultFormValues = () => {
        const domainDefaults = getDomains().map((domain) => domain.getDefaultFormValues());
        const merged = merge({}, DEFAULT_CORE_SEARCH_FORM, ...domainDefaults);
        return merged;
    };

    return (
        <SearchContext.Provider
            value={{
                getFilterComponentsForDomain,
                updateForm,
                results: state.results,
                form: state.form,
                search,
                getDomains,
                getCurrentDomain,
                getDefaultFormValues,
                resetForm,
            }}
        >
            {props.children}
        </SearchContext.Provider>
    );
}

export function useSearchForm<T extends object>() {
    return useContext(SearchContext) as ISearchContextValue<T>;
}

interface ISearchDomain {
    key: string;
    name: string;
    sort: number; // The order the panel appears from left to right
    icon: React.ReactNode;
    PanelComponent: React.ComponentType<any>;
    resultHeader?: React.ReactNode;
    getAllowedFields(): string[];
    getRecordTypes(): string[];
    transformFormToQuery(form: Partial<ISearchForm>): Partial<ISearchRequestQuery>;
    getDefaultFormValues(): Partial<ISearchForm>;
    isIsolatedType(): boolean;
    getSortValues(): ISelectBoxItem[];
    ResultComponent: React.ComponentType<any>;
    ResultWrapper?: React.ComponentType<any>;
    MetaComponent?: React.ComponentType<any>;
    hasSpecificRecord?(form: Partial<ISearchForm>): boolean;
    getSpecificRecord?(form: Partial<ISearchForm>): number;
    SpecificRecordPanel?: React.ComponentType<any>;
    SpecificRecordComponent?: React.ComponentType<any>;
    showSpecificRecordCrumbs?(): boolean; // We could later make this into a config object
}

interface IExtraFilter {
    searchDomain: string;
    filterNode: React.ReactNode;
}

SearchFormContextProvider.extraFilters = [] as IExtraFilter[];

SearchFormContextProvider.addSearchFilter = (domain: string, filterNode: React.ReactNode) => {
    SearchFormContextProvider.extraFilters.push({
        searchDomain: domain,
        filterNode,
    });
};

SearchFormContextProvider.pluggableDomains = [] as ISearchDomain[];
SearchFormContextProvider.addPluggableDomain = (domain: ISearchDomain) => {
    SearchFormContextProvider.pluggableDomains.push(domain);
};

interface ISearchSubType {
    recordType: string;
    icon: React.ReactNode;
    type: string;
    label: string;
}

SearchFormContextProvider.subTypes = {} as Record<string, ISearchSubType>;
SearchFormContextProvider.addSubType = (subType: ISearchSubType) => {
    SearchFormContextProvider.subTypes[subType.type] = subType;
};
SearchFormContextProvider.getSubTypes = (): ISearchSubType[] => {
    return Object.values(SearchFormContextProvider.subTypes);
};
SearchFormContextProvider.getSubType = (type: string): ISearchSubType | null => {
    return SearchFormContextProvider.subTypes[type] ?? null;
};

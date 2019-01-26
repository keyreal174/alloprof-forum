/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { logError } from "@library/utility";

type RecordToggle = (recordType: string, recordID: number) => void;

interface ISiteNavCtx {
    toggleItem: RecordToggle;
    openItem: RecordToggle;
    closeItem: RecordToggle;
    openRecords: {
        [recordType: string]: Set<number>;
    };
}

const noop = () => {
    logError("It looks like you forgot to initialize your SiteNavContext. Be sure to use `<SiteNavProvider />`");
};

const defaultContext: ISiteNavCtx = {
    toggleItem: noop,
    openItem: noop,
    closeItem: noop,
    openRecords: {},
};

export const SiteNavContext = React.createContext<ISiteNavCtx>(defaultContext);

interface IProps {
    children: React.ReactNode;
}

interface IState {
    openRecords: {
        [recordType: string]: Set<number>;
    };
}

export default class SiteNavProvider extends React.Component<{}, IState> {
    public state: IState = {
        openRecords: {},
    };

    public render() {
        return (
            <SiteNavContext.Provider
                value={{
                    openItem: this.openItem,
                    closeItem: this.closeItem,
                    toggleItem: this.toggleItem,
                    openRecords: this.state.openRecords,
                }}
            >
                {this.props.children}
            </SiteNavContext.Provider>
        );
    }

    private openItem = (recordType: string, recordID: number) => {
        const records = this.state.openRecords[recordType] || new Set();
        records.add(recordID);
        this.setState({
            openRecords: {
                ...this.state.openRecords,
                [recordType]: records,
            },
        });
    };

    private closeItem = (recordType: string, recordID: number) => {
        const records = this.state.openRecords[recordType];
        if (!records) {
            return;
        }
        if (records.has(recordID)) {
            records.delete(recordID);
        }
        this.setState({
            openRecords: {
                ...this.state.openRecords,
                [recordType]: records,
            },
        });
    };

    private toggleItem = (recordType: string, recordID: number) => {
        const records = this.state.openRecords[recordType];
        if (records && records.has(recordID)) {
            this.closeItem(recordType, recordID);
        } else {
            this.openItem(recordType, recordID);
        }
    };
}

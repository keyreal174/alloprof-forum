/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";

import { dataListClasses } from "@library/dataLists/dataListStyles";
import { t } from "@vanilla/i18n/src";

export interface IData {
    key: React.ReactNode;
    value: React.ReactNode;
}

export interface IDataList {
    data: IData[];
}

/**
 * Component for displaying data lists
 * Because of accessibility concerns, the markup is a table not a data list.
 */
export function DataList(props: IDataList) {
    const classes = dataListClasses();
    if (props.data.length === 0) {
        return null;
    }
    return (
        <table className={classes.root}>
            <caption>{t("Event Details")}</caption>
            {props.data.map((d, i) => {
                return (
                    <tr className={classes.row} key={i}>
                        <th scope="row" className={classes.key}>
                            {d.key}
                        </th>
                        <td className={classes.value}>{d.value}</td>
                    </tr>
                );
            })}
        </table>
    );
}

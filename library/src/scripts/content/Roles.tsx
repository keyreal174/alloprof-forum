import React from "react";
import { IUser } from "@library/@types/api/users";
import ConditionalWrap from "@library/layout/ConditionalWrap";
import { metasClasses } from "@library/styles/metasStyles";
import { rolesClasses } from "@library/content/rolesStyles";
import classNames from "classnames";

/**
 * Display user role(s)
 */

interface IProps {
    maxRoleCount?: number;
    wrapper?: boolean;
    roles: [
        {
            roleID: number;
            name: string;
        }
    ];
}

export function Roles(props: IProps) {
    const { roles, maxRoleCount = 1, wrapper = true } = props;

    const classesMeta = metasClasses();
    const classes = rolesClasses();

    const userRoles = roles.map((r, i) => {
        if (i < maxRoleCount) {
            return <span className={classNames(classesMeta.meta, classes.role)}>{r.name}</span>;
        }
    });

    return (
        <ConditionalWrap condition={wrapper} className={classesMeta.root}>
            {userRoles}
        </ConditionalWrap>
    );
}

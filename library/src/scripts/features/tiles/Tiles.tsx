/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import Paragraph from "@library/layout/Paragraph";
import classNames from "classnames";
import Tile from "@library/features/tiles/Tile";
import { tilesClasses, tilesVariables } from "@library/features/tiles/tilesStyles";

interface ITile {
    icon: string;
    name: string;
    description: string;
    url: string;
}

interface IProps {
    className?: string;
    items: ITile[];
    title: string;
    titleLevel?: 1 | 2 | 3 | 4 | 5 | 6;
    hiddenTitle?: boolean;
    emptyMessage: string;
    fallbackIcon?: React.ReactNode;
    alignment?: TileAlignment;
    columns?: number;
}

export enum TileAlignment {
    LEFT = "left",
    CENTER = "center",
}

/**
 * Renders list of tiles
 */
export default function Tiles(props: IProps) {
    const optionOverrides = { columns: props.columns, alignment: props.alignment };
    const options = tilesVariables(optionOverrides).options;
    const { className, items } = props;
    const { columns } = options;
    const classes = tilesClasses(optionOverrides);

    if (items.length === 0) {
        return (
            <div className={classNames(className, "isEmpty", classes.root)}>
                <Paragraph>{props.emptyMessage}</Paragraph>
            </div>
        );
    } else {
        return (
            <div className={classNames(className, classes.root)}>
                <ul className={classNames(classes.items)}>
                    {items.map((tile, i) => (
                        <li key={i} className={classNames(classes.item)}>
                            <Tile
                                icon={tile.icon}
                                fallbackIcon={props.fallbackIcon}
                                title={tile.name}
                                description={tile.description}
                                url={tile.url}
                                columns={columns}
                            />
                        </li>
                    ))}
                </ul>
            </div>
        );
    }
}

/**
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { t } from "@library/utility/appUtils";
import Button from "@library/forms/Button";
import { currentThemeClasses } from "./currentThemeStyles";

interface IProps {
    name: string;
    authors: string;
    description: string;
    support?: string;
    onEdit?: React.ReactNode;
    onCopy?: React.ReactNode;
}
interface IState {}

export default class CurrentThemeInfo extends React.Component<IProps, IState> {
    constructor(props) {
        super(props);
    }

    public render() {
        const classes = currentThemeClasses();
        const { name, authors, description, onCopy, onEdit } = this.props;
        return (
            <React.Fragment>
                <section className={classes.themeContainer}>
                    <div className={classes.themeInfo}>
                        <div className={classes.flag}>Current Theme</div>
                        <div className={classes.name}>
                            <h5>{name}</h5>
                        </div>
                        <div className={classes.authorName}>
                            <span>Created By:</span> {authors}
                        </div>

                        <div className={classes.description}>
                            <p>{description}</p>
                        </div>
                    </div>
                    <div className={classes.themeActionButtons}>
                        <Button>{onEdit}</Button>
                        <Button>{onCopy}</Button>
                    </div>
                </section>
            </React.Fragment>
        );
    }
}

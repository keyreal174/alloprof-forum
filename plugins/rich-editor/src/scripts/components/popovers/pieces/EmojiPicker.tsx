/**
 * @author Stéphane (slafleche) LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import React from "react";
import { Grid, AutoSizer } from "react-virtualized";
import classNames from "classnames";
import { t } from "@dashboard/application";
import * as Icons from "@rich-editor/components/icons";
import Popover from "./Popover";
import { IPopoverControllerChildParameters } from "./PopoverController";
import { withEditor, IWithEditorProps } from "@rich-editor/components/context";
import { EMOJI_GROUPS, EMOJIS } from "./emojiData";
import EmojiButton from "./EmojiButton";
import { isEmbedSelected } from "../../../quill/utility";

const buttonSize = 36;
const colSize = 7;
const rowSize = 7;
const rowIndexesByGroupId = {};
const cellIndexesByGroupId = {};

/**
 * Get start positions for each category
 */
EMOJIS.forEach((data, key) => {
    const groupID = data.group;
    if (!(groupID in rowIndexesByGroupId)) {
        rowIndexesByGroupId[groupID] = Math.floor(key / colSize);
        cellIndexesByGroupId[groupID] = key;
    }
});

const emojiGroupLength = Object.values(EMOJI_GROUPS).length;
const numberOfRows = this.getEmojiRowFromIndex(EMOJIS.length);
const elementsOnLastRow = EMOJIS.length % rowSize;

interface IProps extends IWithEditorProps, IPopoverControllerChildParameters {
    contentID: string;
}

interface IState {
    id: string;
    contentID: string;
    scrollTarget: number;
    emojiToFocusPosition: number;
    overscanRowCount: number;
    rowStartIndex: number;
    selectedGroup: number;
    rowEndIndex: number;
    alertMessage?: string;
    title: string;
}

export class EmojiPicker extends React.PureComponent<IProps, IState> {
    private categoryPickerID: string;
    private gridEl: Grid;

    constructor(props) {
        super(props);
        this.state = {
            id: props.id,
            contentID: props.contentID,
            scrollTarget: 0,
            emojiToFocusPosition: 0,
            overscanRowCount: 20,
            rowStartIndex: 0,
            selectedGroup: 0,
            rowEndIndex: 0,
            title: t("Emojis"),
        };

        this.categoryPickerID = "emojiPicker-categories-" + props.editorID;
    }

    get descriptionID(): string {
        return this.state.id + "-description";
    }
    get titleID(): string {
        return this.state.id + "-title";
    }

    public render() {
        const description = [
            t("Insert an emoji in your message."),
            t(
                'Use keyboard shortcuts "page up" and "page down" to cycle through available categories when menu is open.',
            ),
        ].join(" ");

        const extraHeadingContent = (
            <button type="button" className="accessibility-jumpTo" onClick={this.focusOnCategories}>
                {t("Jump past emoji list, to emoji categories.")}
            </button>
        );

        const Icon = <Icons.emoji />;

        const footer = (
            <div id={this.categoryPickerID} className="emojiGroups" aria-label={t("Emoji Categories")} tabIndex={-1}>
                {Object.values(EMOJI_GROUPS).map((groupName: string, groupKey) => {
                    const isSelected = this.state.selectedGroup === groupKey;
                    const buttonClasses = classNames("richEditor-button", "emojiGroup", { isSelected });

                    const onClick = event => this.handleCategoryClick(event, groupKey);

                    return (
                        <button
                            type="button"
                            onClick={onClick}
                            aria-current={isSelected}
                            aria-label={t("Jump to emoji category: ") + t(groupName)}
                            key={"emojiGroup-" + groupName}
                            title={t(groupName)}
                            className={buttonClasses}
                        >
                            {this.getGroupSVGPath(groupName)}
                            <span className="sr-only">{t("Jump to Category: ") + t(groupName)}</span>
                        </button>
                    );
                })}
            </div>
        );

        const grid = (
            <AutoSizer>
                {({ height, width }) => (
                    <Grid
                        containerRole=""
                        cellRenderer={this.cellRenderer}
                        columnCount={colSize}
                        columnWidth={buttonSize}
                        rowCount={numberOfRows}
                        rowHeight={buttonSize}
                        height={height}
                        width={width}
                        overscanRowCount={this.state.overscanRowCount}
                        tabIndex={-1}
                        scrollToAlignment="start"
                        scrollToRow={this.state.scrollTarget}
                        aria-readonly={undefined}
                        aria-label={""}
                        role={""}
                        onScroll={this.handleEmojiScroll}
                        onSectionRendered={this.handleOnSectionRendered}
                        ref={gridEl => {
                            this.gridEl = gridEl as Grid;
                        }}
                    />
                )}
            </AutoSizer>
        );

        return (
            <Popover
                id={this.state.id}
                descriptionID={this.descriptionID}
                titleID={this.titleID}
                title={this.state.title}
                titleRef={this.props.initialFocusRef}
                accessibleDescription={description}
                alertMessage={this.state.alertMessage}
                additionalHeaderContent={extraHeadingContent}
                body={grid}
                footer={footer}
                additionalClassRoot="insertEmoji"
                onCloseClick={this.props.closeMenuHandler}
                isVisible={this.props.isVisible}
            />
        );
    }

    public componentDidMount() {
        document.addEventListener("keydown", this.handleKeyDown, false);
    }

    public componentWillUnmount() {
        document.removeEventListener("keydown", this.handleKeyDown, false);
    }

    /**
     * Handler when new rows are rendered. We use this to figure out what category is current
     */
    private handleOnSectionRendered = event => {
        const rowEndIndex = this.state.rowStartIndex;
        const newRowIndex = event.rowStartIndex;
        let selectedGroup = 0;

        Object.values(rowIndexesByGroupId).map((groupRow, groupKey) => {
            if (newRowIndex >= groupRow) {
                selectedGroup = groupKey;
            }
        });

        this.setState({
            rowStartIndex: event.rowStartIndex,
            rowEndIndex,
            selectedGroup,
            alertMessage: t("In emoji category: ") + t(EMOJI_GROUPS[selectedGroup]),
            title: t(EMOJI_GROUPS[selectedGroup]),
        });
    };

    /**
     * Handle Emoji Scroll
     */
    private handleEmojiScroll = () => {
        this.setState({
            scrollTarget: -1,
            emojiToFocusPosition: -1,
        });
    };

    private handleCategoryClick(event: React.MouseEvent<any>, categoryID: number) {
        event.preventDefault();
        this.scrollToCategory(categoryID);
    }

    /**
     * Scroll to category
     */
    private scrollToCategory = (categoryID: number) => {
        this.setState({
            scrollTarget: rowIndexesByGroupId[categoryID],
            emojiToFocusPosition: cellIndexesByGroupId[categoryID],
            selectedGroup: categoryID,
            alertMessage: t("Jumped to emoji category: ") + t(EMOJI_GROUPS[categoryID]),
        });
    };

    /**
     * Render list row
     */
    private cellRenderer = ({ columnIndex, rowIndex, style }) => {
        const pos = rowIndex * rowSize + columnIndex;
        const emojiData = EMOJIS[pos];
        let result: JSX.Element | null = null;
        const isSelectedButton = this.state.emojiToFocusPosition === pos;

        if (isSelectedButton) {
            window.console.log("");
            window.console.log("isSelectedButton with: ", pos);
            window.console.log("");
        }

        if (emojiData) {
            result = (
                <EmojiButton
                    isSelectedButton={isSelectedButton}
                    style={style}
                    closeMenuHandler={this.props.closeMenuHandler}
                    key={"emoji-" + emojiData.emoji}
                    emojiData={emojiData}
                    index={pos}
                    rowIndex={rowIndex}
                    colIndex={columnIndex}
                    onKeyUp={this.jumpRows}
                    onKeyDown={this.jumpRows}
                />
            );
        }

        return result;
    };

    /**
     * Get Group SVG Path
     */
    private getGroupSVGPath = (groupName: string) => {
        const functionSuffix = groupName.replace(/-([a-z])/g, g => g[1].toUpperCase());
        return Icons["emojiGroup_" + functionSuffix]();
    };

    /**
     * Focus on Emoji Categories
     */
    private focusOnCategories = () => {
        const categories = document.getElementById(this.categoryPickerID);
        if (categories) {
            const firstButton = categories.querySelector(".richEditor-button");
            if (firstButton instanceof HTMLElement) {
                firstButton.focus();
            }
        }
    };

    /**
     * Jump to adjacent category
     *
     * @param offset - How many rows to jump
     */

    private jumpRows = (offset: number) => {
        if (offset !== 0) {
            const activeElement: HTMLElement = document.activeElement as HTMLElement;
            let targetScrollRow = this.state.rowStartIndex;
            let targetFocusRow = targetScrollRow;
            let targetFocusColumn = 0;
            // let forceUpdate = false;
            let emojiFocusPosition = targetFocusRow * rowSize + targetFocusColumn;

            if (
                activeElement &&
                activeElement.classList.contains("richEditor-insertEmoji") &&
                "index" in activeElement.dataset
            ) {
                const index = parseInt(activeElement.dataset.index as "string", 10);
                window.console.log("index :", index);
                // Current position
                targetFocusColumn = this.getEmojiColumnFromIndex(index);
                targetFocusRow = this.getEmojiRowFromIndex(index); // offset focus
            }

            // Check min max values
            emojiFocusPosition = targetFocusRow * rowSize + targetFocusColumn;
            if (emojiFocusPosition < 0) {
                emojiFocusPosition = 0;
            } else if (emojiFocusPosition > EMOJIS.length) {
                emojiFocusPosition = EMOJIS.length;
            }

            // Check scroll position
            if (targetFocusRow < this.state.rowStartIndex) {
                targetScrollRow = targetFocusRow;
            } else if (targetFocusRow > this.state.rowEndIndex) {
                targetScrollRow = targetFocusRow - rowSize;
            }

            window.console.log("");
            window.console.log("this.state.rowStartIndex: ", this.state.rowStartIndex);
            window.console.log("emojiFocusPosition: ", emojiFocusPosition);
            window.console.log("targetFocusColumn :", targetFocusColumn);
            window.console.log("target focus row: ", targetFocusRow);
            window.console.log("");

            this.setState(
                {
                    scrollTarget: targetScrollRow,
                    emojiToFocusPosition: emojiFocusPosition,
                },
                () => {
                    // if (forceUpdate) {
                    //     this.gridEl.forceUpdate();
                    // }
                },
            );
        }
    };

    /**
     * Handle key press.
     *
     * @param event - A synthetic keyboard event.
     */
    private handleKeyDown = (event: KeyboardEvent) => {
        if (this.props.isVisible) {
            switch (event.code) {
                case "PageUp":
                    event.preventDefault();
                    this.jumpRows(-rowSize);
                    break;
                case "PageDown":
                    event.preventDefault();
                    this.jumpRows(rowSize);
                    break;
            }
        }
    };

    /**
     * Make sure target row is within bounds
     *
     * @param targetRow
     */
    private keepRowInBounds(targetRow: number) {
        if (targetRow < 0) {
            return 0;
        } else if (targetRow > numberOfRows) {
            return numberOfRows;
        } else {
            return targetRow;
        }
    }

    /**
     * Make sure target index is within bounds
     *
     * @param targetIndex
     */
    private keepEmojiIndexInBounds(targetIndex: number) {
        if (targetIndex < 0) {
            return 0;
        } else if (targetIndex > numberOfRows) {
            return EMOJIS.length;
        } else {
            return targetIndex;
        }
    }

    /**
     * Get row from Index
     *
     * @param index - Emoji index
     */
    private getEmojiRowFromIndex(index: number) {
        return Math.ceil(index / colSize);
    }

    /**
     * Get column from Index
     *
     * @param index - Emoji index
     */
    private getEmojiColumnFromIndex(index: number) {
        return index % colSize;
    }

    /**
     * Check if emoji index is on last row
     *
     * @param index - Emoji index
     */
    private isEmojiIndexOnLastRow(index: number) {
        const indexOfElementOnLastFullRow = EMOJIS.length - elementsOnLastRow;
        return index > indexOfElementOnLastFullRow;
    }
}

export default withEditor<IProps>(EmojiPicker);

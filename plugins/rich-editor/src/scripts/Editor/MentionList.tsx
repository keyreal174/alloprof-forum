/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import React from "react";
import classNames from "classnames";
import { withEditor, IEditorContextProps } from "./ContextProvider";
import MentionBlot from "../Quill/Blots/Embeds/MentionBlot";
import MentionSuggestion, { IMentionData, MentionSuggestionNotFound } from "./MentionSuggestion";
import { t } from "@core/application";
import { getMentionRange } from "../Quill/utility";
import QuillFlyoutBounds from "./QuillFlyoutBounds";
import Quill, { RangeStatic, DeltaStatic, Sources } from "quill/core";

interface IProps extends IEditorContextProps {
    mentionData: IMentionData[];
    matchedString: string;
    id: string;
    noResultsID: string;
    isVisible: boolean;
    activeItemId: string | null;
    onItemClick: React.MouseEventHandler<any>;
}

interface IState {
    flyoutWidth?: number | null;
    flyoutHeight?: number | null;
    selectionIndex: number | null;
    selectionLength: number | null;
}

class MentionList extends React.PureComponent<IProps, IState> {
    public state = {
        flyoutWidth: null,
        flyoutHeight: null,
        selectionIndex: null,
        selectionLength: null,
    };
    private flyoutRef: React.RefObject<any> = React.createRef();
    private quill: Quill;

    constructor(props) {
        super(props);

        // Quill can directly on the class as it won't ever change in a single instance.
        this.quill = props.quill;
    }

    public componentDidMount() {
        this.setState({
            flyoutWidth: this.flyoutRef.current ? this.flyoutRef.current.offsetWidth : null,
            flyoutHeight: this.flyoutRef.current ? this.flyoutRef.current.offsetHeight : null,
        });
    }

    public render() {
        const { activeItemId, id, onItemClick, matchedString, mentionData, noResultsID, isVisible } = this.props;

        const hasResults = mentionData.length > 0;
        const classes = classNames("atMentionList-items", "MenuItems");

        return (
            <QuillFlyoutBounds
                horizontalAlignment="start"
                verticalAlignment="below"
                flyoutWidth={this.state.flyoutWidth}
                flyoutHeight={this.state.flyoutHeight}
                isActive={isVisible}
                selectionIndex={this.state.selectionIndex}
                selectionLength={this.state.selectionLength}
            >
                {({ x, y }) => {
                    const offset = 3;
                    let style: React.CSSProperties = {
                        visibility: "hidden",
                        position: "absolute",
                        zIndex: -1,
                    };

                    if (x && y) {
                        style = {
                            position: "absolute",
                            top: y.position,
                            left: x.position,
                            zIndex: 1,
                            visibility: "visible",
                        };
                    }

                    return (
                        <span style={style} className="atMentionList" ref={this.flyoutRef}>
                            <ul
                                id={id}
                                aria-label={t("@mention user suggestions")}
                                className={classes + (hasResults ? "" : " isHidden")}
                                role="listbox"
                            >
                                {hasResults &&
                                    mentionData.map(mentionItem => {
                                        const isActive = mentionItem.uniqueID === activeItemId;
                                        return (
                                            <MentionSuggestion
                                                {...mentionItem}
                                                key={mentionItem.name}
                                                onClick={onItemClick}
                                                isActive={isActive}
                                                matchedString={matchedString}
                                            />
                                        );
                                    })}
                            </ul>
                            <div className={classes + (hasResults ? " isHidden" : "")}>
                                <MentionSuggestionNotFound id={noResultsID} />
                            </div>
                        </span>
                    );
                }}
            </QuillFlyoutBounds>
        );
    }

    /**
     * Handle changes from the editor.
     */
    private handleEditorChange = (
        type: string,
        rangeOrDelta: RangeStatic | DeltaStatic,
        oldRangeOrDelta: RangeStatic | DeltaStatic,
        source: Sources,
    ) => {
        const isTextOrSelectionChange = type === Quill.events.SELECTION_CHANGE || type === Quill.events.TEXT_CHANGE;
        if (source === Quill.sources.SILENT || !isTextOrSelectionChange) {
            return;
        }
        const selection: RangeStatic | null = getMentionRange(this.quill, this.quill.getSelection().index);

        if (selection && selection.length > 0) {
            const content = this.quill.getText(selection.index, selection.length);
            const isNewLinesOnly = !content.match(/[^\n]/);

            if (!isNewLinesOnly) {
                this.setState({ selectionIndex: selection.index, selectionLength: selection.length });
                return;
            }
        }

        this.setState({
            selectionIndex: null,
            selectionLength: null,
        });
    };
}

export default withEditor<IProps>(MentionList);

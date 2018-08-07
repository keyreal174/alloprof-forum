/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import React from "react";
import ReactDOM from "react-dom";
import debounce from "lodash/debounce";
import { onContent } from "@dashboard/application";
import { getElementHeight } from "@dashboard/dom";

interface IProps {
    id: string;
    isCollapsed: boolean;
    setNeedsCollapser?: (needsCollapser: boolean) => void;
    dangerouslySetInnerHTML: {
        __html: string;
    };
}

interface IState {
    maxHeight: number | string;
}

export default class CollapsableUserContent extends React.PureComponent<IProps> {
    public state = {
        maxHeight: "100%",
    };
    private selfRef: React.RefObject<HTMLDivElement> = React.createRef();

    public render() {
        const style: React.CSSProperties = this.props.isCollapsed
            ? { maxHeight: this.state.maxHeight, overflow: "hidden" }
            : { maxHeight: this.state.maxHeight };

        return (
            <div
                id={this.props.id}
                className="collapsableContent userContent"
                style={style}
                ref={this.selfRef}
                dangerouslySetInnerHTML={this.props.dangerouslySetInnerHTML}
            />
        );
    }

    public componentDidMount() {
        this.calcMaxHeight();
        window.addEventListener("resize", () =>
            debounce(() => {
                this.calcMaxHeight();
            }, 200)(),
        );
    }

    public componentDidUpdate(prevProps: IProps) {
        if (
            prevProps.dangerouslySetInnerHTML.__html !== this.props.dangerouslySetInnerHTML.__html ||
            prevProps.isCollapsed !== this.props.isCollapsed
        ) {
            this.calcMaxHeight();
        }
    }

    private needsCollapser(maxHeight: number | null): boolean {
        const self = this.selfRef.current;
        return self !== null && self.childElementCount >= 1 && maxHeight !== null && maxHeight >= 100;
    }

    private getNumberMaxHeight(): number | null {
        const self = this.selfRef.current;

        if (!self) {
            return null;
        }

        let finalMaxHeight = 0;
        let lastBottomMargin = 0;
        Array.from(self.children).forEach(child => {
            if (finalMaxHeight > 100) {
                return;
            }

            const { height, bottomMargin } = getElementHeight(child, lastBottomMargin);
            lastBottomMargin = bottomMargin;
            finalMaxHeight += height;
        });
        return finalMaxHeight;
    }

    private calcMaxHeight() {
        const maxHeight = this.getNumberMaxHeight();
        if (this.needsCollapser(maxHeight) && this.props.isCollapsed) {
            this.setState({ maxHeight: maxHeight! });
        } else {
            this.setState({ maxHeight: "100%" });
        }

        this.props.setNeedsCollapser && this.props.setNeedsCollapser(this.needsCollapser(maxHeight));
    }
}

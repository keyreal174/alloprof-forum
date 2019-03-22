/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import Parchment from "parchment";
import Quill, { Blot } from "quill/core";
import Delta from "quill-delta";
import registerQuill from "@rich-editor/quill/registerQuill";
import {
    OrderedListGroup,
    UnorderedListGroup,
    ListValue,
    ListTag,
    ListType,
    ListItem,
    ListGroup,
} from "@rich-editor/quill/blots/blocks/ListBlot";
import { expect } from "chai";

describe.only("ListBlot", () => {
    before(() => {
        registerQuill();
    });

    let quill: Quill;
    let quillNode: HTMLDivElement;

    const resetQuill = () => {
        document.body.innerHTML = `<div id='quill' />`;
        const mountPoint = document.getElementById("quill")!;
        quill = new Quill(mountPoint);
        quillNode = quill.scroll.domNode as HTMLDivElement;
    };

    const insertListBlot = (listValue: ListValue, text: string = "list item"): ListItem => {
        let delta = new Delta();
        if (quill.scroll.length() === 1) {
            delta = delta.delete(1);
        } else {
            delta = delta.retain(quill.scroll.length());
        }

        delta = delta.insert(text + "\n", { list: listValue });
        quill.updateContents(delta, Quill.sources.USER);
        const lastUL = quill.scroll.children.tail as UnorderedListGroup;
        return lastUL.children.tail as any;
    };

    beforeEach(() => {
        resetQuill();
    });

    it("can be created with a bullet list item with all possible value types", () => {
        const testCreateUl = (value: ListValue) => {
            const itemBlot = insertListBlot(value);

            expect(itemBlot.domNode.tagName).eq(ListTag.LI);
            const parent = itemBlot.domNode.parentElement!;
            expect(parent.tagName).eq(ListTag.UL);
            expect(parent.parentElement).eq(quillNode);
        };
        testCreateUl("bullet");
        resetQuill();
        testCreateUl({
            type: ListType.BULLETED,
            depth: 0,
        });
    });

    it("can be created with a simple ordered list item", () => {
        const testCreateOl = (value: ListValue) => {
            const itemBlot = insertListBlot("ordered");

            expect(itemBlot.domNode.tagName).eq(ListTag.LI);
            const parent = itemBlot.domNode.parentElement!;
            expect(parent.tagName).eq(ListTag.OL);
            expect(parent.parentElement).eq(quillNode);
        };

        testCreateOl("ordered");
        resetQuill();
        testCreateOl({
            type: ListType.NUMBERED,
            depth: 0,
        });
    });

    it("always reports it's value in the new object style", () => {
        insertListBlot("ordered");
        insertListBlot("bullet");

        const expected = [
            { insert: "list item" },
            {
                attributes: {
                    list: {
                        type: ListType.NUMBERED,
                        depth: 0,
                    },
                },
                insert: "\n",
            },
            { insert: "list item" },
            {
                attributes: {
                    list: {
                        type: ListType.BULLETED,
                        depth: 0,
                    },
                },
                insert: "\n",
            },
        ];

        expect(quill.getContents().ops).deep.equals(expected);
    });

    it("can be update the depth through quill's formatLine API", () => {
        insertListBlot({ type: ListType.NUMBERED, depth: 0 });
        expect(quill.getContents().ops).deep.equals([
            { insert: "list item" },
            {
                attributes: {
                    list: {
                        type: ListType.NUMBERED,
                        depth: 0,
                    },
                },
                insert: "\n",
            },
        ]);
        quill.formatLine(0, 1, ListItem.blotName, {
            type: ListType.NUMBERED,
            depth: 1,
        });
        expect(quill.getContents().ops).deep.equals([
            { insert: "list item" },
            {
                attributes: {
                    list: {
                        type: ListType.NUMBERED,
                        depth: 1,
                    },
                },
                insert: "\n",
            },
        ]);
    });
    it("list blots of the same type & level are joined together", () => {
        const testAutoJoining = (depth: number, type: ListType.BULLETED) => {
            insertListBlot({ type, depth });
            insertListBlot({ type, depth });
            insertListBlot({ type, depth });
            const listGroup = quill.scroll.children.tail as ListGroup;
            expect(quill.scroll.children.tail).eq(quill.scroll.children.head);
            expect(listGroup).instanceOf(ListGroup);
            expect(listGroup.children).has.length(3);
            resetQuill();
        };

        const depths = [1, 2, 3, 4];
        const types = Object.values(ListType);
        for (const depth of depths) {
            for (const type of types) {
                testAutoJoining(depth, type);
            }
        }
    });

    describe("nests items properly", () => {
        it("different types", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.NUMBERED, depth: 0 });

            expect(quill.scroll.children).has.length(2);
            quill.scroll.children.forEach((blot: ListGroup) => {
                expect(blot).instanceOf(ListGroup);
                expect(blot.children).has.length(1);
                expect(blot.children.head).instanceOf(ListItem);
            });
        });

        it("different levels", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.BULLETED, depth: 1 });
            insertListBlot({ type: ListType.BULLETED, depth: 1 });
            insertListBlot({ type: ListType.BULLETED, depth: 0 });

            // The inner items should be
            // - list item
            // - list item
            //   - list item
            //   - list item
            // - list item

            expect(quill.scroll.children).has.length(1);
            const outerUL = quill.scroll.children.head as ListGroup;
            expect(outerUL).instanceOf(ListGroup);
            expect(outerUL.children).has.length(3);
            const secondChild = outerUL.children.head!.next as ListItem;
            expect(secondChild.children.tail).instanceOf(ListGroup);
        });

        it("can nest different types multiple levels deep", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.NUMBERED, depth: 1 });
            insertListBlot({ type: ListType.NUMBERED, depth: 1 });
            insertListBlot({ type: ListType.BULLETED, depth: 2 });
            insertListBlot({ type: ListType.BULLETED, depth: 3 });
            insertListBlot({ type: ListType.NUMBERED, depth: 4 });
            insertListBlot({ type: ListType.BULLETED, depth: 2 });
            insertListBlot({ type: ListType.NUMBERED, depth: 0 });

            // The inner items should be
            // - list item
            //   1. list item
            //   2. list item
            //      - list item
            //         - list item
            //           - list item
            //      - list item
            // - list item

            expect(quill.scroll.children).has.length(2);

            const depth0UL = quill.scroll.children.head as ListGroup;
            expect(depth0UL).instanceOf(UnorderedListGroup);
            expect(depth0UL.children).has.length(1);
            const depth0LI = depth0UL.children.head as ListItem;

            const depth1OL = depth0LI.children.tail as OrderedListGroup;
            expect(depth1OL).instanceOf(OrderedListGroup);
            expect(depth1OL.children).has.length(2);
            const depth1LI = depth1OL.children.head!.next as ListItem;
            expect(depth1LI).instanceOf(ListItem);

            const depth2UL = depth1LI.children.tail as ListGroup;
            expect(depth2UL).instanceOf(UnorderedListGroup);
            expect(depth2UL.children).has.length(2);
            const depth2LI = depth2UL.children.head as ListItem;

            const depth3UL = depth2LI.children.tail as ListGroup;
            expect(depth3UL).instanceOf(UnorderedListGroup);
            expect(depth3UL.children).has.length(1);
            const depth3LI = depth3UL.children.head as ListItem;

            const depth4UL = depth3LI.children.tail as ListGroup;
            expect(depth4UL).instanceOf(OrderedListGroup);
            expect(depth4UL.children).has.length(1);
        });

        it("item depth can only be > 0 if there are parent items immediately above them.", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 2 });

            expect(quill.scroll.children).has.length(1);
            const UL = quill.scroll.children.head as UnorderedListGroup;
            expect(UL).instanceOf(UnorderedListGroup);
            expect(UL.children).has.length(1);
        });
    });

    describe("indent", () => {
        it("does nothing if we don't have a previous list item to merge into", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 });

            const listGroup = quill.scroll.children.head as ListGroup;
            const listItem = listGroup.children.head as ListItem;
            const contentBefore = quill.getContents().ops;

            listItem.indent();
            quill.update();

            expect(quill.getContents().ops).deep.equals(contentBefore);
        });

        it("can indent an item into the item before it.", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.BULLETED, depth: 0 });

            const listGroup = quill.scroll.children.head as ListGroup;
            let listItem = listGroup.children.tail as ListItem;

            expect(listGroup.children, "List group should have 2 children to start").has.length(2);
            expect(listItem.children, "List item should have 1 child to start").has.length(1);

            listItem.indent();
            // Refetch required due to the optimizations that may have occured on listItem.
            listItem = listGroup.children.tail as ListItem;

            expect(listGroup.children, "List group should have 1 child after").has.length(1);
            expect(listItem.children, "List item should have 2 children after").has.length(2);

            const secondListItem = listItem.children.tail as ListGroup;
            expect(secondListItem).instanceOf(ListGroup);
            expect(secondListItem.getValue().depth, "The first list item should contain a list group").eq(1);
        });

        it("can indent an item into it's own nest list of the same type", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.BULLETED, depth: 1 });

            const listGroup = quill.scroll.children.head as ListGroup;
            let listItem = listGroup.children.tail as ListItem;

            listItem.indent();

            // Expected
            // - listItem
            //   - listItem
            //   - listItem

            expect(listGroup.children, "Only top level list item should remain").has.length(1);
            listItem = listGroup.children.head as ListItem;
            expect(listItem.children.tail, "The first list item should contain a list group").instanceOf(
                UnorderedListGroup,
            );
            const nestedListGroup = listItem.children.tail as UnorderedListGroup;
            expect(nestedListGroup.children, "There should be 2 nested list items").has.length(2);
        });

        it("indenting and outdenting should leave the same dom structure", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.BULLETED, depth: 0 });
            insertListBlot({ type: ListType.BULLETED, depth: 1 });

            const listGroup = quill.scroll.children.head as ListGroup;
            const listItem = listGroup.children.tail as ListItem;
            const htmlBefore = quill.scroll.domNode.innerHTML;

            listItem.indent();
            const topListItem = listGroup.children.tail as ListItem;
            const nestedListGroup = topListItem.children.tail as ListGroup;
            const nestedListItem = nestedListGroup.children.head as ListItem;
            nestedListItem.outdent();
            const htmlAfter = quill.scroll.domNode.innerHTML;
            expect(htmlBefore).equals(htmlAfter);
        });
    });

    describe("descendants", () => {
        it("selects multiple descendant list items properly", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 }, "item1");
            insertListBlot({ type: ListType.BULLETED, depth: 1 }, "item1.1");
            insertListBlot({ type: ListType.BULLETED, depth: 1 }, "item1.2");
            const listGroup = quill.scroll.children.head as ListGroup;

            const descendants = listGroup.descendants((blot: Blot) => blot instanceof ListItem, 6, 10);
            expect(descendants).has.length(2);
        });

        it("selects a single descendant list item properly", () => {
            insertListBlot({ type: ListType.BULLETED, depth: 0 }, "item1");
            insertListBlot({ type: ListType.BULLETED, depth: 1 }, "item1.1");

            const listGroup = quill.scroll.children.head as ListGroup;
            const descendants = listGroup.descendant((blot: Blot) => blot instanceof ListItem, 6);
            expect(descendants).has.length(1);
        });
    });
});

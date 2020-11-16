/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import chai, { expect } from "chai";
import asPromised from "chai-as-promised";
import { flattenObject, spaceshipCompare } from "./logicUtils";
chai.use(asPromised);

describe("spaceshipCompare()", () => {
    it("compares two numbers", () => {
        expect(spaceshipCompare(1, 2)).lessThan(0);
        expect(spaceshipCompare(2, 1)).greaterThan(0);
        expect(spaceshipCompare(1, 1)).equals(0);
    });

    it("compares null to a number", () => {
        expect(spaceshipCompare(null, 1)).lessThan(0);
        expect(spaceshipCompare(1, null)).greaterThan(0);
        expect(spaceshipCompare(null, null)).equals(0);
    });
});

describe("flattenObject()", () => {
    it("flattens objects", () => {
        const initial = {
            key1: "val1",
            nested: {
                nestedKey: "val2",
                array1: ["one", "two", "three"],
            },
        };

        const expected = {
            key1: "val1",
            "nested.nestedKey": "val2",
            "nested.array1.0": "one",
            "nested.array1.1": "two",
            "nested.array1.2": "three",
        };
        expect(flattenObject(initial)).deep.equal(expected);
    });
});

/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

import * as utility from "../utility";

describe("resolvePromisesSequentially()", () => {
    it("resolves promises in order", () => {
        const order: number[] = [];

        const func1 = () => {
            order.push(1);
        };
        const func2 = () => {
            return new Promise(resolve => {
                setTimeout(() => {
                    order.push(2);
                    resolve();
                }, 50);
            });
        };
        const func3 = () => {
            order.push(3);
        };

        const functions = [func1, func2, func3];
        const expectation = [1, 2, 3];

        expect.assertions(1);

        return utility.resolvePromisesSequentially(functions).then(() => {
            expect(order).toEqual(expectation);
        });
    });

    it("returns all of the results in order", () => {
        const func1 = () => Promise.resolve(1);
        const func2 = () => {
            return new Promise(resolve => {
                setTimeout(() => {
                    resolve(2);
                }, 50);
            });
        };
        const func3 = () => Promise.resolve(3);

        const functions = [func1, func2, func3];
        const expectation = [1, 2, 3];

        expect.assertions(1);

        return expect(utility.resolvePromisesSequentially(functions)).resolves.toEqual(expectation);
    });

    it("passes the value of one promise to the next", () => {
        const func = (prev) => Number.isInteger(prev) ? Promise.resolve(prev + 1) : Promise.resolve(0);
        const functions = [func, func, func];
        const expectation = [0, 1, 2];


        return expect(utility.resolvePromisesSequentially(functions)).resolves.toEqual(expectation);
    });
});

describe("hashString", () => {
    test("the same string always results in the same value", () => {
        const str = "a; lksdjfl;aska;lskd fjaskl;dfj al;skdjfalsjkdfa;lksdjfl;kasdjflksaf;kbfjal;skdfbjanv;slkdfjbals;dkjfslkadfj;alsdjf;oiawjef;oiawbejvf;ioawbevf;aoiwebfjaov;wifebvl";
        expect(utility.hashString(str)).toBe(utility.hashString(str));
    });

    test("different strings hash to different values", () => {
        const str1 = "a;slkdfjl;askdjfkl;asdjfkl;asjdfl;";
        const str2 = "a;sldkfjal;skdfjl;kasjdfl;k;laksjdf;laksjdf;laksjdf;lkajsd;lkfjaskl;dfjals;kdfjnal;skdjbfl;kasbdjfv;laskjbdfal;skdjfalv;skdjfalskdbjnfav;bslkdfjnalv;ksdfjbalskdfbjalvsk.dfjbalsv;kdbfjalsv;kdfjbadklsfjals";

        expect(utility.hashString(str1)).not.toBe(utility.hashString(str2));
    });
});

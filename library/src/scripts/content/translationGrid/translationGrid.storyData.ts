/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
import { ITranslationGrid } from "@library/content/translationGrid/TranslationGrid";

export const translationGridData: ITranslationGrid = {
    data: [
        {
            id: "1",
            source: "English",
            translation: "French",
        },

        {
            id: "2",
            source: "English",
            translation:
                "Description Si vous recherchez un ordinateur, vous devez prendre en compte un certain nombre de facteurs. Sera-t-il utilisé pour votre maison, votre bureau ou peut-être même votre combo de bureau à domicile? Tout d'abord, vous devrez définir un budget pour votre nouvel achat avant de décider d'acheter des ordinateurs portables ou de bureau.",
            multiLine: true,
        },

        {
            id: "3",
            source:
                "Description Si vous recherchez un ordinateur, vous devez prendre en compte un certain nombre de facteurs. Sera-t-il utilisé pour votre maison, votre bureau ou peut-être même votre combo de bureau à domicile? Tout d'abord, vous devrez définir un budget pour votre nouvel achat avant de décider d'acheter des ordinateurs portables ou de bureau.",
            translation: "French",
            multiLine: true,
        },

        {
            id: "4",
            source: "Warnings & Notes",
            translation: "",
        },
        {
            id: "5",
            source: "English",
            translation: "",
            multiLine: true,
        },
        {
            id: "6",
            source: "English",
            translation: "French",
        },
        {
            id: "7",
            source: "English",
            translation: "French",
        },

        {
            id: "8",
            source: "English",
            translation: "French",
        },
        {
            id: "9",
            source: "English",
            translation: "French",
        },
        {
            id: "10",
            source: "English",
            translation: "French",
        },
        {
            id: "11",
            source: "English",
            translation: "French",
        },
        {
            id: "12",
            source: "English",
            translation: "French",
        },
        {
            id: "13",
            source: "English",
            translation: "French",
        },
        {
            id: "14",
            source: "English",
            translation: "French",
        },
        {
            id: "15",
            source: "English",
            translation: "French",
        },
        {
            id: "16",
            source: "English",
            translation: "French",
        },
        {
            id: "17",
            source: "English",
            translation: "French",
        },
    ],
    otherLanguages: [
        {
            url: "https://dev.vanilla.localhost/food-en/kb/articles/3-draft-testing",
            locale: "en",
            translationStatus: "out-of-date",
            dateUpdated: "",
        },
        {
            url: "https://dev.vanilla.localhost/food-en/kb/articles/3-draft-testing",
            locale: "fr",
            translationStatus: "not-translated",
            dateUpdated: "",
        },
    ],
    i18nLocales: [
        {
            displayNames: {
                ca: "Anglès",
                en: "English",
                zh: "英文",
            },
            localeID: "en",
            localeKey: "en",
            regionalKey: "en",
        },
        {
            displayNames: {
                ca: "Xinès",
                en: "Chinese",
                zh: "中文",
            },
            localeID: "vf_zh",
            localeKey: "zh",
            regionalKey: "zh",
        },
        {
            displayNames: {
                ca: "Català",
                en: "Catalan",
                zh: "加泰罗尼亚文",
            },
            localeID: "vf_ca",
            localeKey: "ca",
            regionalKey: "ca",
        },
        {
            displayNames: {
                ca: "Francès",
                en: "French",
                fr: "Français",
                zh: "法文",
            },
            localeID: "vf_fr",
            localeKey: "fr",
            regionalKey: "fr",
        },
    ],
};

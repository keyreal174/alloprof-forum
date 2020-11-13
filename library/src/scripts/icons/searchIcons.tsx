/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import classNames from "classnames";
import { iconClasses } from "@library/icons/iconStyles";
import { areaHiddenType } from "@library/styles/styleHelpersVisibility";
import { SearchIcon } from "./titleBar";
import { t } from "@vanilla/i18n/src";

export function TypeAllIcon(props: { className?: string; "aria-hidden"?: areaHiddenType; centred?: boolean }) {
    const classes = iconClasses();
    return <SearchIcon {...props} className={classNames(classes.typeAll, props.className)} />;
}

export function TypeDiscussionsIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeDiscussions, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 18.869 15.804"
        >
            <title>{t("Discussions")}</title>
            <path
                d="M18.911,8.991a7.516,7.516,0,0,0-3.346-1.476c-.607-2.508-3.854-4.13-6.544-4.13h0c-3.8,0-6.952,2.446-6.952,5.572a4.9,4.9,0,0,0,1.453,3.417l-.484,1.97a.772.772,0,0,0,1.092.875l2.242-1.106a8.334,8.334,0,0,0,.894.235,5.371,5.371,0,0,0,1.8,2.522,7.876,7.876,0,0,0,4.92,1.63,8.447,8.447,0,0,0,2.649-.417l2.242,1.106.608-2.845a4.932,4.932,0,0,0,1.453-3.417A5.038,5.038,0,0,0,18.911,8.991ZM6.321,13.018l-2.533,1.51.764-2.4A4.184,4.184,0,0,1,3.062,8.96c0-2.652,2.546-4.58,5.959-4.58s5.362,1.986,5.362,2.98c0,.007.017.022.024.032-.141-.006-.278-.033-.421-.033A7.877,7.877,0,0,0,9.061,8.99a5.038,5.038,0,0,0-2.027,3.94c0,.134.013.265.024.4C6.831,13.238,6.616,13.129,6.321,13.018Zm12.361,2.729-.106.1-.4,1.886L16.7,17.011l-.2.07a7.4,7.4,0,0,1-2.515.43,6.881,6.881,0,0,1-4.3-1.415A4.073,4.073,0,0,1,8.027,12.93,4.068,4.068,0,0,1,9.682,9.765a6.88,6.88,0,0,1,4.3-1.414,6.867,6.867,0,0,1,4.3,1.415,4.066,4.066,0,0,1,1.655,3.164A3.923,3.923,0,0,1,18.682,15.747Z"
                transform="translate(-2.069 -3.385)"
                style={{ fill: "currentColor" }}
            />
        </svg>
    );
}

export function TypeArticlesIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeArticles, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 14.666 14.666"
        >
            <title>{t("Articles")}</title>
            <path
                d="M17.417,3.667a.916.916,0,0,1,.916.916h0V17.417a.916.916,0,0,1-.916.916H4.583a.916.916,0,0,1-.916-.916h0V4.583a.916.916,0,0,1,.916-.916Zm0,.916H4.583V17.417H17.417ZM12.833,13.75v.917H6.417V13.75Zm2.75-1.833v.916H6.417v-.916ZM11,6.417V11H6.417V6.417Zm4.583,3.666V11H11.917v-.917Zm-5.5-2.75H7.333v2.75h2.75Zm5.5.917v.917H11.917V8.25Zm0-1.833v.916H11.917V6.417Z"
                transform="translate(-3.667 -3.667)"
                style={{ fill: "currentColor", fillRule: "evenodd" }}
            />
        </svg>
    );
}

export function TypeCategoriesAndGroupsIcon(props: {
    className?: string;
    "aria-hidden"?: areaHiddenType;
    title?: string;
}) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeCategoriesAndGroups, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 15.122 16.416"
        >
            <title>{props.title ?? t("Categories & Groups")}</title>
            <path
                d="M3.283,13.983l7.534,4.073L18.2,13.725l-3.218-1.77c-.687.34-1.727.9-2.578,1.362a15.322,15.322,0,0,1-1.664.813c-.1,0-3-1.457-4.217-2.164Z"
                transform="translate(-3.078 -1.64)"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: "0.833px" }}
            />
            <path
                d="M14.653,7.952S10.844,10,10.749,10L6.811,7.974c-.886.487-3.548,1.989-3.733,2.076l7.739,4.186L18.2,9.9Z"
                transform="translate(-3.078 -1.64)"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: "0.833px" }}
            />
            <path
                d="M10.687,1.64,3.3,5.973l7.385,4.06L18.2,5.7Z"
                transform="translate(-3.078 -1.64)"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: "0.833px" }}
            />
        </svg>
    );
}

export function TypeMemberIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeMember, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
        >
            <title>{t("Members")}</title>
            <path
                d="M13.49,11.415l.064.015a8.462,8.462,0,0,1,4.354,3.113,11.459,11.459,0,0,1-.034,3.156,1.016,1.016,0,0,1-.756.3H2.861a1.016,1.016,0,0,1-.756-.3,11.428,11.428,0,0,1,.013-3.156A9.494,9.494,0,0,1,6.471,11.43a.232.232,0,0,1,.193.012A7.144,7.144,0,0,0,9.99,12.675a7.339,7.339,0,0,0,3.372-1.233.234.234,0,0,1,.192-.012ZM10.012,2c1.974,0,3.575,1.029,3.575,4.395,0,2.427-1.6,4.394-3.575,4.394S6.437,8.822,6.437,6.4C6.437,3.029,8.038,2,10.013,2Z"
                style={{ fill: "none", stroke: "currentColor" }}
            />
        </svg>
    );
}

export function TypeCategoriesIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    return <TypeCategoriesAndGroupsIcon {...props} title={t("Categories")} />;
}

export function TypeIdeasIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeIdeasIcon, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 18.444 16.791"
        >
            <title>{t("Ideas")}</title>
            <path
                d="M14.618,20.686a.388.388,0,0,1,0,.753H11.381a.387.387,0,0,1,0-.753h3.237Zm.79-1.793a.377.377,0,1,1,0,.753H10.592a.377.377,0,1,1,0-.753h4.817ZM13,5.148a5.544,5.544,0,0,1,5.623,5.434,5.406,5.406,0,0,1-1.861,4.028l-.06.058a4.1,4.1,0,0,0-.79,2.718.367.367,0,0,1-.1.254.341.341,0,0,1-.243.1H10.411a.385.385,0,0,1-.243-.1.319.319,0,0,1-.1-.254,3.988,3.988,0,0,0-.79-2.718,5.4,5.4,0,0,1-1.9-4.085A5.546,5.546,0,0,1,13,5.148Zm-.02.645a4.849,4.849,0,0,0-4.936,4.77,4.7,4.7,0,0,0,1.7,3.6,4.44,4.44,0,0,1,1.01,2.893h4.47a4.471,4.471,0,0,1,.951-2.815c0-.02.04-.058.061-.078a4.68,4.68,0,0,0,1.679-3.6A4.848,4.848,0,0,0,12.979,5.793Zm-.472.77a4.345,4.345,0,0,1,4.522,4.187.357.357,0,0,1-.712,0,3.677,3.677,0,0,0-3.81-3.527.331.331,0,1,1,0-.66Z"
                transform="translate(-3.84 -4.898)"
                style={{ fill: "currentColor", stroke: "currentColor", strokeWidth: "0.5px" }}
            />
            <path
                d="M19.658,5.213a.542.542,0,0,1,.766.767L19.341,7.063a.542.542,0,1,1-.766-.766l1.083-1.083ZM22.28,9.979a.542.542,0,0,1-.471.6l-1.521.186a.542.542,0,1,1-.132-1.075l1.52-.187A.542.542,0,0,1,22.28,9.979ZM4.438,9.38l1.524.16a.542.542,0,0,1-.113,1.078l-1.524-.16a.542.542,0,0,1,.113-1.079ZM5.521,5.316a.541.541,0,0,1,.762-.08L7.474,6.2a.542.542,0,0,1-.682.842L5.6,6.077a.54.54,0,0,1-.08-.761Z"
                transform="translate(-3.84 -4.898)"
                style={{ fill: "currentColor", fillRule: "evenodd" }}
            />
        </svg>
    );
}

export function TypePollsIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typePollsIcon, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 26 26"
        >
            <title>{t("Polls")}</title>
            <rect
                x="4.603"
                y="14.818"
                width="8.938"
                height="4.063"
                rx="1.3"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: "0.9750000238418579px" }}
            />
            <rect
                x="4.604"
                y="6.987"
                width="5.958"
                height="4.063"
                rx="1.3"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: "0.9750000238418579px" }}
            />
            <path
                d="M18.211,9.062H13.955"
                style={{
                    fill: "none",
                    stroke: "currentColor",
                    strokeLinecap: "round",
                    strokeLinejoin: "round",
                    strokeWidth: "1.4620000123977661px",
                }}
            />
            <path
                d="M12.083,9.062l2.043-2.031v4.063Z"
                style={{
                    fill: "currentColor",
                    stroke: "currentColor",
                    strokeLinejoin: "round",
                    strokeWidth: "0.7310000061988831px;fill-rule: evenodd",
                }}
            />
            <path
                d="M15.4,16.681h4.256"
                style={{
                    fill: "none",
                    stroke: "currentColor",
                    strokeLinecap: "round",
                    strokeLinejoin: "round",
                    strokeWidth: "1.4620000123977661px",
                }}
            />
            <path
                d="M21.526,16.681l-2.043,2.031V14.649Z"
                style={{
                    fill: "currentColor",
                    stroke: "currentColor",
                    strokeLinejoin: "round",
                    strokeWidth: "0.7310000061988831px;fill-rule: evenodd",
                }}
            />
        </svg>
    );
}

export function TypeQuestionIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeQuestion, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 26 26"
        >
            <title>{t("Question")}</title>
            <path
                d="M13.245,15.427v-.34a1.756,1.756,0,0,1,.238-.973,4.438,4.438,0,0,1,.94-.914,8.084,8.084,0,0,0,1.159-1.042,2.689,2.689,0,0,0,.668-1.849,2.465,2.465,0,0,0-.868-2,3.547,3.547,0,0,0-2.348-.726,6.653,6.653,0,0,0-3.284.853l.634,1.33a9.357,9.357,0,0,1,1.193-.492,4.2,4.2,0,0,1,1.329-.2,1.9,1.9,0,0,1,1.215.34,1.174,1.174,0,0,1,.423.973,1.655,1.655,0,0,1-.26.936,5.8,5.8,0,0,1-1.16,1.1,3.941,3.941,0,0,0-1.056,1.14,2.844,2.844,0,0,0-.3,1.382v.483h1.479ZM12.565,19a1.074,1.074,0,0,0,.828-.313,1.205,1.205,0,0,0,.29-.85,1.159,1.159,0,0,0-.287-.841,1.093,1.093,0,0,0-.83-.3,1,1,0,0,0-1.11,1.14,1.2,1.2,0,0,0,.287.857,1.074,1.074,0,0,0,.823.306Z"
                style={{ fill: "currentColor" }}
            />
            <circle
                cx="13"
                cy="13"
                r="8.667"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: "1.083px" }}
            />
        </svg>
    );
}

export function TypePlacesIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typePlaces, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
        >
            <title>{t("Places")}</title>
            <path
                strokeLinecap="round"
                d="M0.595 0.417L0.595 14.5"
                transform="translate(5) rotate(-11 12.731 -4.133)"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: ".833px" }}
            />
            <path
                d="M2.305 1.25L7.916 1.25 5.63 3.847 7.916 6.445 0.643 6.445 0.643 1.25z"
                transform="translate(5) rotate(-11 12.731 -4.133)"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: ".833px" }}
            />
            <ellipse
                cx="4.286"
                cy="16.429"
                rx="4.286"
                ry="1.429"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: ".833px" }}
                transform="translate(5)"
            />
        </svg>
    );
}

export function TypeGroupsIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeGroups, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 26 26"
        >
            <title>{t("Groups")}</title>
            <circle cx="13" cy="13" r="13" style={{ fill: "none" }} />
            <path
                d="M18.7,8.5c1.1,0,2.1,0.6,2.1,2.7c0,1.5-0.9,2.7-2.1,2.7s-2.1-1.2-2.1-2.7C16.6,9.1,17.6,8.5,18.7,8.5z
		 M12.9,5.8c1.6,0,3,0.9,3,3.8c0,2.1-1.3,3.8-3,3.8c-1.6,0-3-1.7-3-3.8C9.9,6.7,11.3,5.8,12.9,5.8z M7.2,8.5c1.2,0,2.1,0.6,2.1,2.7
		c0,1.5-0.9,2.7-2.1,2.7s-2.1-1.2-2.1-2.7C5.1,9.1,6,8.5,7.2,8.5z M22.9,18.4h-3.3c0,0.5,0,0.9-0.1,1c-0.1,0.2-0.4,0.3-0.6,0.3H7
		c-0.3,0-0.5-0.1-0.6-0.3c-0.1-0.1-0.1-0.5-0.1-1H3c-0.2,0-0.3-0.1-0.4-0.2c-0.1-0.1-0.1-1.8,0-1.9c0.5-0.8,1.4-1.4,2.5-1.9h0.1
		c0.5,0.3,1.2,0.7,1.9,0.7c0.4,0,0.8-0.1,1.1-0.3c0.5-0.3,1-0.6,1.7-0.9c0.1,0,0.1,0,0.2,0c0.7,0.4,1.7,1.1,2.8,1.1s2.1-0.7,2.8-1.1
		c0,0,0.1,0,0.2,0c0.7,0.2,1.3,0.5,1.9,1c0.3,0.1,0.6,0.2,0.9,0.2c0.8,0,1.5-0.5,2-0.7h0.1c0.9,0.2,1.8,0.9,2.5,1.9
        c0.1,0.1,0.1,1.8,0,1.9C23.2,18.3,23,18.4,22.9,18.4z"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: ".867px" }}
            />
            <path
                d="M19.6,18.4c0-0.7,0-1.6-0.1-1.7c-0.5-0.7-1.1-1.4-1.7-1.8"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: ".867px" }}
            />
            <path
                d="M8,14.9c-0.8,0.5-1.2,1.1-1.6,1.8c-0.1,0.1-0.1,1-0.1,1.7"
                style={{ fill: "none", stroke: "currentColor", strokeWidth: ".867px" }}
            />
        </svg>
    );
}

export function TypeKnowledgeBaseIcon(props: { className?: string; "aria-hidden"?: areaHiddenType }) {
    const classes = iconClasses();
    return (
        <svg
            aria-hidden={props["aria-hidden"] !== undefined ? props["aria-hidden"] : "true"}
            className={classNames(classes.typeKnowledgeBase, props.className)}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 26 26"
        >
            <title>{t("Knowledge Base")}</title>
            <circle cx="13" cy="13" r="13" style={{ fill: "none" }} />
            <path
                strokeWidth=".867"
                d="M2.37 0L2.37 11.598"
                style={{ fill: "none", stroke: "currentColor" }}
                transform="translate(6.5 5.417)"
            />
            <rect
                width="12.323"
                height="14.275"
                x=".474"
                strokeWidth=".867"
                rx="1.083"
                style={{ fill: "none", stroke: "currentColor" }}
                transform="translate(6.5 5.417)"
            />
            <path
                strokeWidth=".867"
                d="M.474 13.382s0-1.784 1.896-1.784H10.9c1.264 0 1.896-.595 1.896-1.784"
                style={{ fill: "none", stroke: "currentColor" }}
                transform="translate(6.5 5.417)"
            />
            <path
                strokeLinecap="round"
                strokeWidth=".975"
                d="M4.74 4.015L9.479 4.015M4.74 5.799L9.479 5.799"
                style={{ fill: "none", stroke: "currentColor" }}
                transform="translate(6.5 5.417)"
            />
            <path
                strokeWidth=".867"
                d="M3.318 11.598L3.318 15.931 4.266 15.065 5.214 15.931 5.214 11.598z"
                style={{ fill: "none", stroke: "currentColor" }}
                transform="translate(6.5 5.417)"
            />
        </svg>
    );
}

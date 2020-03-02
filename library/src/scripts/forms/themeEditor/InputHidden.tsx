/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";

export interface IInputHidden extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "type"> {}

export default function InputHidden(props: IInputHidden) {
    return <input type="hidden" {...props} />;
}

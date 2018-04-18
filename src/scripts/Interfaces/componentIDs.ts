import uniqueid from "lodash/uniqueid";

// Optional ID
export interface IOptionalComponentID {
    id?: string | boolean;
}

// Requires ID
export interface IRequiredComponentID {
    id?: string;
}

export function uniqueIDFromPrefix(suffix: string) {
    return (suffix + uniqueid()) as string;
}

export function getRequiredID(props: IRequiredComponentID, suffix: string) {
    if (props.id) {
        return props.id;
    } else {
        return uniqueIDFromPrefix(suffix);
    }
}

export function getOptionalID(props: IOptionalComponentID, suffix?: string): string | null {
    if (props.id) {
        // we want an ID
        if (typeof props.id === "string") {
            // Handled by parent component
            return props.id;
        } else if (typeof props.id === "boolean" && suffix) {
            return uniqueIDFromPrefix(suffix);
        }
        throw new Error("To generate and ID, you must provide a suffix.");
    } else {
        return null;
    }
}

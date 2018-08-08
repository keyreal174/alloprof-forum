export interface IScrapeData {
    type: string;
    url: string;
    name?: string | null;
    body?: string | null;
    photoUrl?: string | null;
    height?: number | null;
    width?: number | null;
    attributes: {
        [key: string]: any;
    };
}

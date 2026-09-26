export interface Category {
    id: number;
    name: Record<string, string>;
    slug?: string;
    description?: Record<string, string>;
    sort_order?: number;
}

export interface Tag {
    id: number;
    name: Record<string, string>;
    slug?: string;
}

export interface CoverImage {
    id: number;
    url: string;
}

export interface Post {
    id: number;
    title: string;
    summary?: string;
    content: string;
    status: string;
    language: string;
    language_label: string;
    is_pinned: boolean;
    view_count: number;
    published_at: string | null;
    created_at: string;
    cover_image?: CoverImage;
    category?: Category;
    tags?: Tag[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
}

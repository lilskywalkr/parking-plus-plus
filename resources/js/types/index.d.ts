import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface ParkingSpace {
    id: number;
    available: boolean;
    user_id?: number | null;
    registration_plate?: string | null;
    created_at: string;
    updated_at: string;
}

export interface Record {
    id: number;
    user: string;
    parking_space_id: number;
    registration_plates?: string | null;
    action: string;
    date: string;
    time: string;
}

export interface RecordPagination {
    data: Record[];
    links: {
        first: string | null;
        last: string | null;
        next: string | null;
        prev: string | null;
    },
    meta: {
        current_page: number;
        current_page_url: string;
        from: number,
        path: string,
        per_page: number,
        to: number,
    }
}

export interface Order {
    id: number;
    status: string;
    registration_plates: string[];
    total_price: number;
    payment_summarized: boolean;
    session_id: string;
    customer_email: string;
    customer_name: string;
    created_at: number;
}

export type BreadcrumbItemType = BreadcrumbItem;

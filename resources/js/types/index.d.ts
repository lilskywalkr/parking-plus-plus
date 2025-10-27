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
    user_id: number;
    user: User;
    parking_space_id: number;
    parking_space?: ParkingSpace;
    registration_plates?: string | null;
    action: string;
    created_at: string;
    updated_at: string;
}

export interface RecordPagination {
    current_page: number;
    current_page_url: string;
    data: Record[];
    first_page_url: string;
    from: number;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
}

export type BreadcrumbItemType = BreadcrumbItem;

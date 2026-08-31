export interface Auth {
    user: {
        id: number;
        name: string;
        email: string;
        role: string | null;
        image_url: string | null;
        permissions: string[];
    } | null;
}

export interface MenuItem {
    label: string;
    icon: string;
    url: string | null;
    active: boolean;
}

export interface MenuBlock {
    header: string | null;
    /** false pins the group open and hides its collapse control. */
    collapsible?: boolean;
    items: MenuItem[];
}

export interface Flash {
    success: string | null;
    error: string | null;
}

export interface NotificationSummary {
    unread_count: number;
    can_view: boolean;
}

export interface PageProps {
    auth: Auth;
    menu: MenuBlock[];
    flash: Flash;
    notifications?: NotificationSummary;
    [key: string]: unknown;
}

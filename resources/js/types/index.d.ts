export interface Auth {
    user: {
        id: number;
        name: string;
        email: string;
        role: string | null;
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
    items: MenuItem[];
}

export interface Flash {
    success: string | null;
    error: string | null;
}

export interface PageProps {
    auth: Auth;
    menu: MenuBlock[];
    flash: Flash;
    [key: string]: unknown;
}

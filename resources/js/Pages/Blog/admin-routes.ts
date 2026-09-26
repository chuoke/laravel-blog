import { usePage } from '@inertiajs/vue3';

type BlogRoutes = {
    admin: string;
    api: string;
};

const path = (prefix: string, suffix = ''): string => {
    const base = prefix.replace(/\/$/, '');
    const segment = suffix.replace(/^\//, '');

    return segment ? `${base}/${segment}` : base;
};

export const useBlogRoutes = () => {
    const page = usePage<{ blog: { routes: BlogRoutes } }>();

    return {
        adminUrl: (suffix = ''): string => path(page.props.blog.routes.admin, suffix),
        apiUrl: (suffix = ''): string => path(page.props.blog.routes.api, suffix),
    };
};

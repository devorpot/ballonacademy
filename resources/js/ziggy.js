import { route as ziggyRoute } from 'ziggy-js';

export function route(name, params = {}, absolute = true) {
    return ziggyRoute(name, params, absolute, Ziggy);
}

export const Ziggy = {
    routes: window.Ziggy?.routes || {},
    url: window.Ziggy?.url || '',
    port: window.Ziggy?.port || null,
    defaults: window.Ziggy?.defaults || {},
};
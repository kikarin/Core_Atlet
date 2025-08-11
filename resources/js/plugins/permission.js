import { permission, can } from '@/directives/permission';

export default {
    install(app) {
        app.directive('permission', permission);
        app.directive('can', can);
        
        app.config.globalProperties.$permission = {
            hasPermission: () => {
                return true;
            },
            can: () => {
                return true;
            }
        };
    }
};

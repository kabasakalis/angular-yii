angular.module('app').config([
    '$httpProvider','X_REST_USERNAME','X_REST_PASSWORD',
    function ($httpProvider,X_REST_USERNAME,X_REST_PASSWORD) {
        $httpProvider.defaults.headers.common['X_REST_USERNAME'] = X_REST_USERNAME;
        $httpProvider.defaults.headers.common['X_REST_PASSWORD'] = X_REST_PASSWORD;
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }
]);

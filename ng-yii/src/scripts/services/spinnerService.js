angular.module('app').factory('spinnerService', ['$rootScope', '$log',
    function ($rootScope, $log) {
        var spinnerService = {};
        spinnerService.registerListener = function () {
            $rootScope.$on('spinner', function (event, options) {
                $rootScope.spinneroptions = options;
            });
        }
        spinnerService.broadcastSpinner = function (options) {
            $rootScope.$broadcast('spinner', options);
        }
        return spinnerService
    }
]);
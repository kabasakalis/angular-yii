angular.module('app').factory('notifyService', ['$rootScope',
    function ($rootScope) {
        var notifyService = {};
        notifyService.registerListener = function () {
            $rootScope.$on('notify', function (event, message) {
                $rootScope.notyoptions = message;
            });
        }
        notifyService.broadcastMessage = function (message) {
            $rootScope.$broadcast('notify', message);
        }
        return notifyService
    }
]);
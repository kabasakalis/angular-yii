angular.module('app').directive('fancybox', [
    '$log', function ($log) {
        return{
            restrict: 'A',
            link: function (scope, element, attrs) {
                $(document).ready(function () {
                    var default_options = {
                        prevEffect: 'fade',
                        nextEffect: 'fade',
                        closeBtn: true,
                        helpers: {
                            title: { type: 'over' },
                            buttons: {},
                            thumbs: {
                                width: 50,
                                height: 50
                            }
                        }
                    };
                    var passedOptions;
                    if (attrs.fancybox)passedOptions = attrs.fancybox; else passedOptions = {};
                    var options = angular.extend(default_options, angular.fromJson(passedOptions));
                    var id = $(element).attr('id');
                    $("." + id).fancybox(options);
                });
            }
        }
    }
]);

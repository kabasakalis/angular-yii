        angular.module('app').directive('skSpinner', [
            '$log', function ($log) {
                return {
                    restrict: 'A',
                    scope: {spinneroptions: "="},
                    link: function (scope, element, attrs) {
                        var clearSpinner = function (container) {
                            while (container.firstChild) {
                                container.removeChild(container.firstChild);
                            }
                        }

//for options see http://fgnass.github.io/spin.js/#!
                        var opts_default = {
                            lines: 13, // The number of lines to draw
                            length: 20, // The length of each line
                            width: 10, // The line thickness
                            radius: 30, // The radius of the inner circle
                            corners: 1, // Corner roundness (0..1)
                            rotate: 0, // The rotation offset
                            direction: 1, // 1: clockwise, -1: counterclockwise
                            color: '#00ff00', // #rgb or #rrggbb
                            speed: 1, // Rounds per second
                            trail: 60, // Afterglow percentage
                            shadow: false, // Whether to render a shadow
                            hwaccel: false, // Whether to use hardware acceleration
                            className: 'spinner', // The CSS class to assign to the spinner
                            zIndex: 2e9, // The z-index (defaults to 2000000000)
                            top: 'auto', // Top position relative to parent in px
                            left: 'auto',// Left position relative to parent in px
                            //position: 'absolute'
                        };
                        scope.$watch('spinneroptions', function (val) {
                            var target = angular.element(element)[0];
                            var mySpinner;
                            if (val != null) {
                                clearSpinner(target);

                                var opts = angular.extend(opts_default, angular.fromJson(val));
                                mySpinner = new window.Spinner(opts).spin(target);
                                //   target.appendChild(mySpinner.el);
                            } else {
                                clearSpinner(target);
                            }
                        })
                    }
                }
            }
        ]);
  //  });
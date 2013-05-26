/*These are all CSS3 keyframe animations (see stylesheet)
 * At the moment of this writing  ngAnimate is very fresh in angular and does not support
 *  keyframe animations as CSS animations,so we apply this workaround.
 *  For more on angular.js animation  http://www.yearofmoo.com/2013/04/animation-in-angularjs.html
 * */
angular.module('app').animation('float', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation
                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 1000000;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('flipLeftToRight-enter', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                },
                start: function (element, done, memo) {
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('flipLeftToRight-leave', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation

                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('flipup-enter', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                    //prepare the element for animation
                    //      element.css({ 'opacity': 1, 'height':0});
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation

                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }


        }
    ])
    .animation('flipup-leave', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                    //prepare the element for animation
                    //      element.css({ 'opacity': 1, 'height':0});
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation

                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('newspaper-enter', [
        '$log', function ($log) {
            return {
                setup: function (element) {

                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation
                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('newspaper-leave', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation

                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('sides-enter', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                    //prepare the element for animation
                    //      element.css({ 'opacity': 1, 'height':0});
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation

                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('sides-leave', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                    //prepare the element for animation
                    //      element.css({ 'opacity': 1, 'height':0});
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation

                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 600;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])
    .animation('fall-leave', [
        '$log', function ($log) {
            return {
                setup: function (element) {
                },
                start: function (element, done, memo) {
                    //do nothing since CSS3 does the animation

                    //this is the total duration of your animation code
                    var TOTAL_DURATION = 15000;
                    setTimeout(done, TOTAL_DURATION);
                }
            }
        }
    ])

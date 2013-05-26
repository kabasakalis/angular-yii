var app, dir, express, io, port, routes, server, _ref, _ref1;

express = require('express');

routes = require('./routes');

dir = "" + __dirname + "/dev";

port = (_ref = (_ref1 = process.env.PORT) != null ? _ref1 : process.argv.splice(2)[0]) != null ? _ref : 3005;

app = express();


server = require('http').createServer(app);

io = require('socket.io').listen(server);

app.configure(function() {
  app.use(require('grunt-contrib-livereload/lib/utils').livereloadSnippet);
  app.use(express.logger('dev'));
  app.use(express.bodyParser());

  app.use(express.methodOverride());

    // ## CORS middleware
    // see: http://stackoverflow.com/questions/7067966/how-to-allow-cors-in-express-nodejs
    var allowCrossDomain = function(req, res, next) {
        res.header('Access-Control-Allow-Origin', '*');
        res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
        res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization,X_REST_USERNAME,X_REST_PASSWORD');
        res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization,X_REST_USERNAME,X_REST_PASSWORD');

        // intercept OPTIONS method
        if ('OPTIONS' == req.method) {
          res.send(200);
        }
        else {
          next();
        }
    };
//    app.use(allowCrossDomain);

  app.use(express.errorHandler());

  app.use(express["static"](dir));
  app.use(app.router);

  return routes(app, dir);
});

module.exports = server;

module.exports.use = function() {
  return app.use.apply(app, arguments);
};

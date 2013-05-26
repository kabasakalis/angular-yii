
module.exports = function(app, dir) {
  app.get('/', function(req, res) {
   return res.render("" + dir + "/index.html");
  });
};

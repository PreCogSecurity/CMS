process.env.DISABLE_NOTIFIER = true

var elixir = require('laravel-elixir');

stylesPath = 'public/assets/styles/';
scriptsPath = 'public/assets/scripts/';

elixir.config.css.outputFolder = stylesPath;
elixir.config.js.outputFolder = scriptsPath;
elixir.config.sourcemaps = false;

elixir(function (mix) {
    mix.copy('resources/assets/css/bootstrap.*.min.css', stylesPath);
    mix.styles(['bootstrap.min.css'], stylesPath + 'bootstrap.default.min.css');
    mix.styles(['bootstrap.min.css', 'bootstrap-theme.min.css'], stylesPath + 'bootstrap.legacy.min.css');

    mix.styles(['cms-main.css'], stylesPath + 'cms-main.css');

    // the csrf helper has to be first in every bundle, the ajax calls below
    // rely on it to attach the token to their requests
    mix.scripts(['cms-csrf.js', 'cms-timeago.js', 'cms-restfulizer.js', 'cms-carousel.js', 'cms-alerts.js'], scriptsPath + 'cms-main.js');
    mix.scripts(['cms-picker.js'], scriptsPath + 'cms-picker.js');
    mix.scripts(['cms-csrf.js', 'cms-comment-core.js', 'cms-comment-edit.js', 'cms-comment-delete.js', 'cms-comment-create.js', 'cms-comment-fetch.js', 'cms-comment-main.js'], scriptsPath + 'cms-comment.js');
});

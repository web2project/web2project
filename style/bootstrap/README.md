# web2Project Bootstrap theme

This theme renders web2Project with [Bootstrap](https://getbootstrap.com/) 5.3.8
using Bootstrap's default colours and components together with the standard
web2Project logos.

* `css/bootstrap.min.css` and `js/bootstrap.bundle.min.js` are the unmodified
  Bootstrap 5.3.8 distribution files (MIT licensed, see `BOOTSTRAP-LICENSE`),
  bundled so the theme works without access to a CDN.
* `main.css` maps the class names used throughout web2Project (`std`, `tbl`,
  `list`, `text`, `button`, `hilite`, ...) onto the Bootstrap look and holds
  the application specific styles (task status colours, calendars, tooltips).
* `overrides.php` defines the `style_bootstrap` theme class and a `CTabBox`
  which renders Bootstrap nav-tabs.
* `header.php`, `footer.php`, `login.php` and `lostpass.php` are the page
  shell templates. Every other template comes from `style/_common`.
* Images which are not part of this theme are loaded from
  `style/web2project/images`.

This is the only theme in use: the `W2P_UISTYLE` constant in `base.php` selects
it, and there is no per-user or system-wide style setting.

To upgrade Bootstrap, replace the files in `css/` and `js/` with the matching
files from the `dist/` directory of a newer 5.3.x release.

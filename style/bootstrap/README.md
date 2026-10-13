# web2Project Bootstrap theme

This theme renders web2Project with [Bootstrap](https://getbootstrap.com/) 5.3.8,
styled after the NiceAdmin admin template (BootstrapMade): its colours, fonts,
card and form styles and its header-and-sidebar layout, together with the
standard web2Project logos. No NiceAdmin code or assets are included.

* `css/bootstrap.min.css` and `js/bootstrap.bundle.min.js` are the unmodified
  Bootstrap 5.3.8 distribution files (MIT licensed, see `BOOTSTRAP-LICENSE`),
  bundled so the theme works without access to a CDN.
* `fonts/` holds Nunito Sans (body text) and Poppins (headings) from
  Fontsource 5.3.0, SIL Open Font License 1.1 (`fonts/OFL-*.txt`). Only the
  weights and subsets the theme uses are included; `fonts/fonts.css` declares
  them with `unicode-range`, so browsers download just the subsets a page needs.
* `icons/` is the unmodified Bootstrap Icons 1.13.1 web font (MIT licensed, see
  `icons/LICENSE`), used for the navigation and header actions.
* `main.css` maps the class names used throughout web2Project (`std`, `tbl`,
  `list`, `text`, `button`, `hilite`, ...) onto the Bootstrap look and holds
  the application specific styles (task status colours, calendars, tooltips).
* `overrides.php` defines the `style_bootstrap` theme class and a `CTabBox`
  which renders Bootstrap nav-tabs.
* `header.php` and `footer.php` are the page shell: a fixed header (sidebar
  toggle, logo, global search, the New Item menu, quick links and the user
  menu), the module sidebar built by `style_bootstrap::buildHeaderNavigation()`
  and a footer with the version. `js/theme.js` collapses the sidebar to icons
  on wide screens (remembered in localStorage) and slides it in on narrow ones.
  `login.php` and `lostpass.php` are the sign-in pages. Every other template
  comes from `style/_common`.
* Images which are not part of this theme are loaded from
  `style/web2project/images`.

This is the only theme in use: the `W2P_UISTYLE` constant in `base.php` selects
it, and there is no per-user or system-wide style setting.

To upgrade Bootstrap, replace the files in `css/` and `js/` with the matching
files from the `dist/` directory of a newer 5.3.x release.

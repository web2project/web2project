# Demo data and documentation screenshots

Builds a throwaway web2project instance with realistic sample data, the
"Build Family Home - 1420 Cedar Lane" project, and captures the screenshots
used by the docs site (`web2project.github.io/assets/docs`) and the manual
(`web2project-manual/manuscript/images`).

All data goes in through the web UI, using the same forms, CSRF nonces and
`do_*_aed.php` handlers as a browser. The seed therefore also works as a
smoke test of the add/edit screens.

## What gets created

| Area | Sample data |
|---|---|
| System | Company name "Oak Hollow Builders", admin email, external sign-up enabled, Forums/Departments/Resources modules switched on, "Project worker" role granted access/view/add/edit on non-admin modules |
| Companies | Oak Hollow Builders (GC), Brightline Electric, ClearFlow Plumbing, Summit Heating & Air, Nair Design Studio, Riverbend County Building Dept. Each has a "Contractor License #" custom field |
| Departments, billing codes | Site Operations, Estimating & Purchasing; SITE / SUPV / EQUIP codes |
| Users | `admin` (renamed Jordan Avery, homeowner); active `dwhitfield`, `mlee`, `rdelgado`, `pnair`; inactive (no role) `tbrennan`, `lortega`. Password `buildit2026` |
| Contacts | One per user plus a county building inspector and an estimator, all with phone/email/job title |
| Project | In progress, Sep 2026 - Jun 2027, linked to the contacts |
| Tasks | 16 tasks with 19 dependencies, multiple assignees, resources and task logs |
| Other | 5 hardware-store links, 10 calendar events with attendees, 3 file folders with 9 placeholder PDFs, 2 forums with threaded posts |

## Usage

Requirements: PHP 8.x CLI (mysqli, gd, mbstring, xml), MariaDB/MySQL running
locally with root access through the `mysql` client, composer, and Python 3
with `requests`, `playwright` (Chromium) and `Pillow`.

```bash
# WARNING: drops/recreates the w2p database and removes includes/config.php
./setup.sh /path/to/web2project

# capture every docs/manual screenshot (+ thumb-*.png) into ./shots
python3 screenshots.py --out ./shots

# ...and copy them over the existing images in the docs site and manual
python3 screenshots.py --out ./shots \
    --docs-site ../../../web2project.github.io --manual ../../../web2project-manual
```

The four installer screenshots (`installer/*.png`) need an unconfigured copy
of web2project, so they come from a separate script. It serves that copy on
port 8081 against its own database (`w2p_inst`) and leaves the main instance
alone. The setup commands are in the script's docstring; then:

```bash
python3 installer_screenshots.py --out ./shots
```

Run the capture once, straight after `setup.sh`. Every capture run logs
in again, and those sessions show up in `users/sessions.png`.

Then log in at http://127.0.0.1:8080 as `admin` / `passw0rd`.

Environment overrides: `W2P_URL`, `W2P_DB`, `W2P_DB_USER`, `W2P_DB_PASS`,
`W2P_ADMIN_PASS`, `W2P_TIMEZONE` (default `Atlantic/Reykjavik`, UTC+0; see below).

## Screenshot conventions

- 1600x1206 viewport, the same 1.327 aspect ratio as the original 1052x793
  shots, so the `thumb-*.png` files stay 250x188. The two widest tables
  (`tasks/index.png`, `events/today_tasks.png`) and the translation table use
  1920x1447.
- `system-admin/systemconfig_1..3.png` are three slices of one full-page capture.
- Autofocus rings and autofocus scrolling are cleared before each capture.

## Workarounds for 5.0 behaviour (reported separately)

- **Project worker role:** a fresh install grants it only the login
  permission. The seed adds access/view/add/edit on non-admin modules.
- **Timezone:** the System Timezone and user Timezone dropdowns have no
  blank/UTC choice, so saving either form stores the first option
  (Africa/Algiers). Times are converted using the system zone when saved but
  the user zone when shown, and date-only fields (project dates) are shown a
  day early in any zone west of UTC. The seed sets both to a UTC+0 zone.
- **Gantt charts show only the viewer's tasks:** `tasks/viewgantt.php` and
  `projectdesigner/vw_gantt.php` build the chart image URL without the `f=`
  filter, and the `showInactive=1` parameter is missing its `&`. The capture
  rewrites the image `src` to add `&f=all` and fixes the separator
  (`gantt_all_tasks` in `screenshots.py`).
- **Budgets:** the budget reports and budget fields only appear while
  System Configuration > "budget_info_display" is on. The seed turns it on,
  enters budgets, then turns it off. `screenshots.py` turns it on for the
  shots marked `budget=True`.
- **Reports:** "Allocated User Hours" needs user `0` (all users) to show
  anything. "Overall" collapses task logs by cost code, so its totals are
  too low. "Tasks End Date" filters on `task_dynamic = 0` and is always
  empty with default task settings, so it isn't captured.
- **File folders:** the Add File form has no folder picker, so the seed posts
  `file_folder` directly.

Images that cannot be regenerated are listed at the top of
`screenshots.py`.

## Files

- `setup.sh`: fresh database, PHP built-in server, web installer, seed
- `seed.py`: the sample data
- `w2p_client.py`: form-driven HTTP client used by the seed
- `installer_screenshots.py`: the four web-installer screens from an
  unconfigured copy
- `screenshots.py`: Playwright capture of each documented screen; the
  `SHOTS` table maps every image path to the page and interaction behind it

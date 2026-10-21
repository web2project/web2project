#!/usr/bin/env python3
"""Capture the documentation screenshots from a seeded web2project instance.

    python3 screenshots.py --out ./shots [--only tasks/] [--docs-site PATH] [--manual PATH]

Writes <out>/<path>.png plus <out>/<dir>/thumb-<name>.png (250x188) for every
entry in SHOTS. With --docs-site / --manual each image is also copied to
web2project.github.io/assets/docs (image + thumbnail) and
web2project-manual/manuscript/images (image only; the manual has no
thumbnails), but only when a page there references it.

The installer screens need their own, unconfigured copy of web2project and are
captured by installer_screenshots.py.
"""
import argparse
import os
import shutil
from datetime import datetime

from PIL import Image
from playwright.sync_api import sync_playwright

from w2p_client import W2P

URL = os.environ.get('W2P_URL', 'http://127.0.0.1:8080')
INDEX = URL + '/index.php'
PASSWORDS = {'admin': os.environ.get('W2P_ADMIN_PASS', 'passw0rd')}
DEFAULT_PASS = 'buildit2026'
# Same 1.327 aspect ratio as the original 1052x793 shots, so the 250x188
# thumbnails keep their size; wide enough that list tables fit beside the sidebar.
VIEWPORT = {'width': 1600, 'height': 1206}
WIDE = {'width': 1920, 'height': 1447}   # same ratio, for the widest tables
POPUP = {'width': 800, 'height': 603}    # same ratio, for small popup windows
THUMB = (250, 188)


# ------------------------------------------------------------- interactions --
def click_text(label, scope='a, button, li, span'):
    def act(pg):
        loc = pg.locator(scope).filter(has_text=label)
        for i in range(loc.count()):
            el = loc.nth(i)
            if el.is_visible() and el.inner_text().strip() == label:
                el.click()
                pg.wait_for_timeout(400)
                return
        raise RuntimeError('no visible element labelled %r' % label)
    return act


def select(selector, value):
    def act(pg):
        with pg.expect_navigation():
            pg.select_option(selector, value)
        pg.wait_for_load_state('networkidle')
    return act


def hover(selector):
    def act(pg):
        pg.hover(selector)
        pg.wait_for_timeout(800)
    act.keep_mouse = True  # don't move the pointer away; the tooltip must stay open
    return act


def submit_form(selector):
    def act(pg):
        with pg.expect_navigation():
            pg.click(selector)
        pg.wait_for_load_state('networkidle')
    return act


def lost_password(pg):
    # same as clicking "Help! I've forgotten my password!"
    with pg.expect_navigation():
        pg.evaluate('() => { const f = document.loginform; f.lostpass.value = 1; f.submit(); }')
    pg.wait_for_load_state('networkidle')


def report(start=None, end=None, checks=(), values=None):
    """Fill a report's period and options the way the calendar pickers do, then submit."""
    def act(pg):
        fields = dict(values or {})
        for prefix, day in (('start', start), ('end', end)):
            if day:
                d = datetime.strptime(day, '%Y-%m-%d')
                fields['log_%s_date' % prefix] = d.strftime('%Y%m%d')
                fields['%s_date' % prefix] = d.strftime('%d/%b/%Y')
        pg.evaluate('''([fields, checks]) => {
            const f = document.editFrm;
            for (const [k, v] of Object.entries(fields)) { if (f[k]) f[k].value = v; }
            for (const k of checks) { if (f[k]) f[k].checked = true; }
        }''', [fields, list(checks)])
        with pg.expect_navigation():
            pg.click('form[name=editFrm] input[name=do_report]')
        pg.wait_for_load_state('networkidle')
    return act


def gantt_loaded(pg):
    """Wait until the Gantt chart images have finished loading."""
    pg.wait_for_function("""() => [...document.querySelectorAll('img[src*="a=gantt"]')]
                                  .every(i => i.complete)""")


def steps(*actions):
    def act(pg):
        for a in actions:
            a(pg)
    act.keep_mouse = any(getattr(a, 'keep_mouse', False) for a in actions)
    return act


def js(script):
    def act(pg):
        pg.evaluate(script)
        pg.wait_for_timeout(400)
    return act


def smartsearch(term, options=False):
    def act(pg):
        pg.fill('form[name=frmSearch] input[name=keyword1]', term)
        with pg.expect_navigation():
            pg.press('form[name=frmSearch] input[name=keyword1]', 'Enter')
        pg.wait_for_load_state('networkidle')
        if options:  # show the "Modules selection" and "Advanced search" option blocks
            for name in ('modselection', 'advancedsearch'):
                pg.check('form[name=frmSearch] input[name=%s]' % name)
            pg.wait_for_timeout(400)
    return act


def add_task_rows(*names):
    """ProjectDesigner Add Tasks panel: press the green + once per row and name it."""
    def act(pg):
        for i, name in enumerate(names):
            pg.click('#addtasks a:has(img), #addtasks img[src*="add"]', timeout=5000)
            pg.wait_for_timeout(300)
            boxes = pg.locator('#addtasks input[name^="add_task_name"]')
            boxes.nth(boxes.count() - 1).fill(name)
    return act


# Pages remember each user's last tab, so every page with tabs names its tab explicitly.
# (image path, user or None for logged out, query or absolute path, interaction[, options])
# options: viewport=<dict>; crop=<0..1> takes a full-page capture and crops one
# viewport-sized slice at that fraction of the scrollable height; element=<css>
# captures just that element (via its closest panel); budget=True captures with
# Display Budget Information switched on.
SHOTS = [
    ('companies/addedit.png', 'admin', 'm=companies&a=addedit&company_id=1', None),
    ('companies/index.png', 'admin', 'm=companies', None),
    ('companies/view.png', 'admin', 'm=companies&a=view&company_id=1&tab=0', None),
    ('contacts/addedit.png', 'admin', 'm=contacts&a=addedit&contact_id=2', None),
    ('contacts/index.png', 'admin', 'm=contacts', None),
    ('contacts/vcard_import.png', 'admin', 'm=contacts&a=vcardimport&dialog=0', None),
    ('departments/company_tab.png', 'admin', 'm=companies&a=view&company_id=1&tab=3', None),
    ('departments/addedit.png', 'admin', 'm=departments&a=addedit&dept_id=1', None),
    ('events/addedit.png', 'admin', 'm=events&a=addedit&event_id=3', None),
    ('events/today_events.png', 'admin', 'm=events&a=day_view&date=20261013&tab=0', None),
    ('events/today_tasks.png', 'dwhitfield', 'm=events&a=day_view&date=20261013&tab=1', None, {'viewport': WIDE}),
    ('events/view_day.png', 'admin', 'm=events&a=day_view&date=20261028&tab=0', None),
    ('events/view_week.png', 'admin', 'm=events&a=week_view&date=20261013', None),
    ('events/view_month.png', 'admin', 'm=events&date=20261013', None),
    ('events/view_month_tt.png', 'admin', 'm=events&date=20261013',
     hover('a:has-text("Groundbreaking")')),
    ('events/view_year.png', 'admin', 'm=events&a=year_view&date=20261013', None),
    ('files/addedit.png', 'admin', 'm=files&a=addedit&file_id=1', None),
    ('files/index.png', 'admin', 'm=files&tab=0', None),
    ('forums/addedit.png', 'admin', 'm=forums&a=addedit&forum_id=1', None),
    ('forums/index.png', 'admin', 'm=forums', None),
    ('forums/view.png', 'admin', 'm=forums&a=view&forum_id=1', None),
    ('forums/thread.png', 'admin', 'm=forums&a=view&forum_id=1&message_id=1', None),
    ('getting-started/layout.png', 'admin', 'm=events&a=day_view&date=20261013&tab=0',
     click_text('New Item', scope='button, a')),
    ('getting-started/change_password.png', 'admin', 'm=public&a=chpwd&dialog=1&user_id=1', None,
     {'viewport': POPUP}),
    ('help/window.png', 'admin', 'm=help&dialog=1&hid=', None, {'viewport': POPUP}),
    ('history/index.png', 'admin', 'm=history', select('form[name=filter0] select[name=filter]', 'tasks')),
    ('history/project_tab.png', 'admin', 'm=projects&a=view&project_id=1&tab=8', None),
    ('links/addedit.png', 'admin', 'm=links&a=addedit&link_id=1', None),
    ('links/index.png', 'admin', 'm=links', None),
    ('misc/login.png', None, '/index.php', None),
    ('misc/forgotten_password.png', None, '/index.php', lost_password),
    ('misc/new_account.png', None, '/newuser.php', None),
    ('projectdesigner/index.png', 'admin', 'm=projectdesigner&project_id=1', gantt_loaded),
    ('projectdesigner/project.png', 'admin', 'm=projectdesigner&project_id=1', None, {'element': '#project'}),
    ('projectdesigner/gantt.png', 'admin', 'm=projectdesigner&project_id=1', gantt_loaded, {'element': '#gantt'}),
    ('projectdesigner/tasks.png', 'admin', 'm=projectdesigner&project_id=1', None, {'element': '#tasks'}),
    ('projectdesigner/actions.png', 'admin', 'm=projectdesigner&project_id=1', None, {'element': '#actions'}),
    ('projectdesigner/addtasks.png', 'admin', 'm=projectdesigner&project_id=1',
     add_task_rows('Order kitchen cabinets', 'Schedule gutter install'), {'element': '#addtasks'}),
    ('projectdesigner/files.png', 'admin', 'm=projectdesigner&project_id=1', None, {'element': '#files'}),
    ('projects/addedit.png', 'admin', 'm=projects&a=addedit&project_id=1', None),
    ('projects/index.png', 'admin', 'm=projects&tab=1', None),
    ('projects/view.png', 'admin', 'm=projects&a=view&project_id=1&tab=0', None),
    ('reports/index.png', 'admin', 'm=reports&project_id=1', None),
    ('reports/allocated_user_hours.png', 'admin', 'm=reports&project_id=1&report_type=allocateduserhours',
     report('2026-10-05', '2026-11-29', values={'user_id': '0'}), {'viewport': WIDE}),
    ('reports/overall.png', 'admin', 'm=reports&project_id=1&report_type=overall',
     report('2026-09-01', '2026-10-31')),
    ('reports/project_statistics.png', 'admin', 'm=reports&project_id=1&report_type=stats', None),
    ('reports/tasks_end_date.png', 'admin', 'm=reports&project_id=1&report_type=taskenddate',
     report('2026-10-01', '2026-12-31', values={'user_id': '2'})),
    ('reports/task_list.png', 'admin', 'm=reports&project_id=1&report_type=tasklist',
     report('2026-10-01', '2026-12-31')),
    ('reports/task_log_cost_codes.png', 'admin', 'm=reports&project_id=1&report_type=tasklogs',
     report('2026-09-01', '2026-10-31')),
    ('reports/task_log_valued.png', 'admin', 'm=reports&project_id=1&report_type=tasklogs_xp1',
     report('2026-09-01', '2026-10-31')),
    ('reports/tasks_by_user.png', 'admin', 'm=reports&project_id=1&report_type=tasksperuser',
     report('2026-10-01', '2026-12-31')),
    ('reports/user_performance.png', 'admin', 'm=reports&project_id=1&report_type=userperformance',
     report('2026-09-01', '2026-10-31')),
    ('resources/addedit.png', 'admin', 'm=resources&a=addedit&resource_id=1', None),
    ('resources/index.png', 'admin', 'm=resources', None),
    ('resources/view.png', 'admin', 'm=resources&a=view&resource_id=1', None),
    ('resources/task_tab.png', 'admin', 'm=tasks&a=view&task_id=4&tab=4', None),
    ('smartsearch/index.png', 'admin', 'm=smartsearch', smartsearch('footing')),
    ('smartsearch/options.png', 'admin', 'm=smartsearch', smartsearch('footing', options=True)),
    ('system-admin/billing_code.png', 'admin', 'm=system&a=billingcode&company_id=1', None),
    ('system-admin/cfe_addedit.png', 'admin', 'm=system&u=customfields&a=addedit&module=1&field_id=1', None),
    ('system-admin/cfe_index.png', 'admin', 'm=system&u=customfields', None),
    ('system-admin/default_user_preferences.png', 'admin', 'm=system&a=addeditpref', None),
    ('system-admin/index.png', 'admin', 'm=system', None),
    ('system-admin/permissions.png', 'admin', 'm=system&a=acls_view', None),
    ('system-admin/roles.png', 'admin', 'm=system&u=roles', None),
    ('system-admin/role_permissions.png', 'admin', 'm=system&u=roles&a=viewrole&role_id=14', None),
    ('system-admin/system_lookup_keys.png', 'admin', 'm=system&u=syskeys&a=keys', None),
    ('system-admin/system_lookup_values.png', 'admin', 'm=system&u=syskeys', None),
    ('system-admin/systemconfig_1.png', 'admin', 'm=system&a=systemconfig', None, {'crop': 0.0}),
    ('system-admin/systemconfig_2.png', 'admin', 'm=system&a=systemconfig', None, {'crop': 0.5}),
    ('system-admin/systemconfig_3.png', 'admin', 'm=system&a=systemconfig', None, {'crop': 1.0}),
    ('system-admin/translation.png', 'admin', 'm=system&a=translate&module=common&lang=fr', None, {'viewport': WIDE}),
    ('system-admin/upgrade.png', 'admin', 'm=system&u=upgrade', None),
    ('system-admin/view_modules.png', 'admin', 'm=system&u=modules', None),
    ('tasks/addedit_1.png', 'admin', 'm=tasks&a=addedit&task_id=12', click_text('Details')),
    ('tasks/addedit_2.png', 'admin', 'm=tasks&a=addedit&task_id=12', click_text('Dates')),
    ('tasks/addedit_3.png', 'admin', 'm=tasks&a=addedit&task_id=12', click_text('Dependencies')),
    ('tasks/addedit_4.png', 'admin', 'm=tasks&a=addedit&task_id=12', click_text('Human Resources')),
    ('tasks/addedit_5.png', 'admin', 'm=tasks&a=addedit&task_id=4', click_text('Other resources')),
    ('tasks/gantt.png', 'admin', 'm=projects&a=view&project_id=1&tab=3',
     steps(submit_form('a:has-text("show full project")'), gantt_loaded), {'viewport': WIDE, 'crop': 1.0}),
    ('tasks/index.png', 'admin', 'm=tasks', select('select[name=f]', 'all'), {'viewport': WIDE}),
    ('tasks/log.png', 'dwhitfield', 'm=tasks&a=view&task_id=4&tab=1', None, {'crop': 1.0}),
    ('tasks/view.png', 'admin', 'm=tasks&a=view&task_id=4&tab=0', None),
    ('tasks/todo_tasks.png', 'dwhitfield', 'm=tasks&a=todo&tab=0', None),
    ('tasks/todo_gantt.png', 'dwhitfield', 'm=tasks&a=todo&tab=1', None),
    ('users/active.png', 'admin', 'm=users&tab=0', None),
    ('users/inactive.png', 'admin', 'm=users&tab=1', None),
    ('users/userlog.png', 'admin', 'm=users&tab=2', submit_form('form[name=frmDate] input[type=submit]')),
    ('users/sessions.png', 'admin', 'm=users&tab=3', None),
    ('users/addedit.png', 'admin', 'm=users&a=addedit&user_id=2', None),
    ('users/user_prefs.png', 'admin', 'm=system&a=addeditpref&user_id=2', None),
    ('users/view.png', 'admin', 'm=users&a=view&user_id=2&tab=0', submit_form('form[name=frmDate] input[type=submit]')),
    ('users/permissions.png', 'admin', 'm=users&a=view&user_id=2&tab=1', None),
    ('users/roles.png', 'admin', 'm=users&a=view&user_id=2&tab=2', None),
    # captured last, with Display Budget Information switched on
    ('system-admin/budgeting.png', 'admin', 'm=system&a=budgeting', None, {'budget': True}),
    ('reports/budgets_by_category.png', 'admin', 'm=reports&project_id=1&report_type=budgetcategorybyprojectcost',
     report('2026-09-01', '2027-06-30', values={'company_id': '1'}), {'budget': True, 'viewport': WIDE}),
    ('reports/costs_by_project.png', 'admin', 'm=reports&project_id=1&report_type=costsbyproject',
     report('2026-09-01', '2027-06-30', values={'company_id': '1'}), {'budget': True}),
    ('reports/costs_by_task.png', 'admin', 'm=reports&project_id=1&report_type=costsbytask',
     report('2026-09-01', '2027-06-30'), {'budget': True}),
]


# Documented images that cannot be regenerated from web2project 5.0:
#   contacts/email_confirm.png      the "ask contact to update" email is never sent
#                                   (CContact::updateNotify() has no caller)
#   files/explorer.png              Folder Explorer view is unwired; folders_table.php fatals
#   system-admin/import.png         LDAP contact import now says "This functionality has been
#                                   removed pending a complete rewrite."
#   holiday/*.jpg                   Holiday add-on module, not in core
#   misc/yahoo-widget.jpg           third-party desktop widget


def login(browser, user, viewport=VIEWPORT):
    ctx = browser.new_context(viewport=viewport)
    pg = ctx.new_page()
    pg.goto(INDEX)
    if user:
        pg.fill('#username', user)
        pg.fill('#password', PASSWORDS.get(user, DEFAULT_PASS))
        pg.press('#password', 'Enter')
        pg.wait_for_load_state('networkidle')
    return ctx, pg


def thumbnail(src, dest):
    """250x188 thumbnail; images of another shape are letterboxed rather than squashed."""
    im = Image.open(src).convert('RGB')
    if abs(im.width / im.height - THUMB[0] / THUMB[1]) < 0.02:
        im.resize(THUMB, Image.LANCZOS).save(dest, optimize=True)
        return
    im.thumbnail(THUMB, Image.LANCZOS)
    canvas = Image.new('RGB', THUMB, (244, 246, 251))
    canvas.paste(im, ((THUMB[0] - im.width) // 2, (THUMB[1] - im.height) // 2))
    canvas.save(dest, optimize=True)


def set_budget_display(on):
    from seed import set_budget_display as toggle
    toggle(W2P('admin', PASSWORDS['admin']).login(), on)


def capture(out, only=None):
    done, failed = [], []
    budget_on = False
    with sync_playwright() as p:
        browser = p.chromium.launch()
        pages = {}
        try:
            for path, user, query, action, *rest in SHOTS:
                opts = rest[0] if rest else {}
                if only and not path.startswith(only):
                    continue
                if opts.get('budget') and not budget_on:
                    set_budget_display(True)
                    budget_on = True
                viewport = opts.get('viewport', VIEWPORT)
                key = (user, viewport['width'])
                # logged-out screens get a fresh context each time
                if user is None or key not in pages:
                    ctx, pg = login(browser, user, viewport)
                    if user:
                        pages[key] = (ctx, pg)
                else:
                    ctx, pg = pages[key]
                try:
                    pg.goto(URL + query if query.startswith('/') else INDEX + '?' + query)
                    pg.wait_for_load_state('networkidle')
                    if action:
                        action(pg)
                except Exception as exc:  # keep going; report at the end
                    print('FAILED', path, str(exc).splitlines()[0])
                    failed.append(path)
                    continue
                # drop autofocus rings and undo the scroll autofocus can cause
                pg.evaluate('''() => {
                    document.activeElement && document.activeElement.blur();
                    document.documentElement.style.scrollBehavior = 'auto';  // no smooth-scroll animation
                    window.scrollTo(0, 0);
                    document.querySelectorAll('*').forEach(e => { if (e.scrollLeft) e.scrollLeft = 0; if (e.scrollTop) e.scrollTop = 0; });
                }''')
                pg.wait_for_timeout(200)
                pg.evaluate('() => window.scrollTo(0, 0)')  # again, after late focus/scroll handlers
                if not getattr(action, 'keep_mouse', False):
                    pg.mouse.move(viewport['width'] - 5, viewport['height'] - 5)
                dest = os.path.join(out, path)
                os.makedirs(os.path.dirname(dest), exist_ok=True)
                if 'element' in opts:
                    # the panel body plus its heading row
                    panel = pg.evaluate_handle(
                        '(sel) => { const e = document.querySelector(sel);'
                        ' return e.closest(".card, .std, fieldset, table.std") || e.parentElement; }', opts['element'])
                    panel.as_element().screenshot(path=dest)
                elif 'crop' in opts:
                    pg.screenshot(path=dest, full_page=True)
                    full = Image.open(dest)
                    top = int((full.height - viewport['height']) * opts['crop'])
                    full.crop((0, top, viewport['width'], top + viewport['height'])).save(dest)
                else:
                    pg.screenshot(path=dest)
                thumbnail(dest, os.path.join(os.path.dirname(dest), 'thumb-' + os.path.basename(dest)))
                done.append(path)
                print('captured', path)
                if user is None:
                    ctx.close()
        finally:
            browser.close()
            if budget_on:
                set_budget_display(False)
    if failed:
        print('failed:', ', '.join(failed))
    return done


def referenced(root, exts, needle):
    """True if any source file under root mentions needle."""
    for dirpath, dirnames, files in os.walk(root):
        dirnames[:] = [d for d in dirnames if d not in ('_site', '.git', 'vendor', 'assets', 'images')]
        for f in files:
            if f.endswith(exts):
                with open(os.path.join(dirpath, f), encoding='utf-8', errors='ignore') as fh:
                    if needle in fh.read():
                        return True
    return False


def publish(out, done, docs_site=None, manual=None):
    """Copy each image to the docs site and/or manual, but only where a page uses it."""
    for path in done:
        thumb = os.path.join(os.path.dirname(path), 'thumb-' + os.path.basename(path))
        targets = []
        if docs_site and referenced(docs_site, ('.md', '.html'), '/assets/docs/' + path):
            root = os.path.join(docs_site, 'assets', 'docs')
            targets += [(path, root), (thumb, root)]
        if manual and referenced(os.path.join(manual, 'manuscript'), ('.txt',), 'images/' + path):
            targets.append((path, os.path.join(manual, 'manuscript', 'images')))
        for name, root in targets:
            dst = os.path.join(root, name)
            os.makedirs(os.path.dirname(dst), exist_ok=True)
            shutil.copyfile(os.path.join(out, name), dst)
            print('wrote', dst)


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('--out', default='shots')
    ap.add_argument('--only')
    ap.add_argument('--docs-site')
    ap.add_argument('--manual')
    args = ap.parse_args()
    captured = capture(args.out, args.only)
    publish(args.out, captured, args.docs_site, args.manual)

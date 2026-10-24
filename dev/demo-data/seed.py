#!/usr/bin/env python3
"""Load the "Build Family Home - 1420 Cedar Lane" sample data into a fresh
web2project install, entirely through the web UI (see w2p_client.py).

Usage:  python3 seed.py --install     # run the web installer first
        python3 seed.py               # data only (install already done)
"""
import os
import re
import sys
import zlib

import requests

from w2p_client import BASE_URL, INDEX, W2P, sql_id

ADMIN_PASS = os.environ.get('W2P_ADMIN_PASS', 'passw0rd')
# UTC+0 with no daylight saving. With any other zone, 5.0 shows date-only fields
# (project start/end dates) a day early in lists and views, because they are
# stored unconverted but converted to the user's zone on display.
TIMEZONE = os.environ.get('W2P_TIMEZONE', 'Atlantic/Reykjavik')
USER_PASS = 'buildit2026'
HERE = os.path.dirname(os.path.abspath(__file__))


def step(title):
    print('==', title)


# ---------------------------------------------------------------- install --
def install():
    step('web installer')
    r = requests.post(BASE_URL + '/install/index.php', data=dict(
        step='perform', dbhost=os.environ.get('W2P_DB_HOST', '127.0.0.1'),
        dbname=os.environ.get('W2P_DB', 'w2p'), dbuser=os.environ.get('W2P_DB_USER', 'w2p'),
        dbpass=os.environ.get('W2P_DB_PASS', 'w2p'), adminpass=ADMIN_PASS, do_db_cfg='1'))
    if 'Config file written successfully' not in r.text:
        sys.exit('installer did not report success')


# ------------------------------------------------------------- helpers -----
def placeholder_pdf(path, title, lines):
    """Write a small one-page PDF without third-party libraries."""
    def esc(t):
        return t.replace('\\', '\\\\').replace('(', '\\(').replace(')', '\\)')
    ops = ['BT /F2 20 Tf 72 700 Td (%s) Tj ET' % esc(title)]
    y = 670
    for line in lines:
        ops.append('BT /F1 12 Tf 72 %d Td (%s) Tj ET' % (y, esc(line)))
        y -= 18
    ops += ['0.6 G 72 200 468 360 re S',
            'BT /F1 11 Tf 210 380 Td ([ drawing / content placeholder ]) Tj ET']
    stream = zlib.compress('\n'.join(ops).encode('latin-1'))
    objs = [b'<< /Type /Catalog /Pages 2 0 R >>',
            b'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            b'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R '
            b'/Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>',
            b'<< /Length %d /Filter /FlateDecode >>\nstream\n' % len(stream) + stream + b'\nendstream',
            b'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            b'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>']
    out = b'%PDF-1.4\n'
    offsets = []
    for i, o in enumerate(objs, 1):
        offsets.append(len(out))
        out += b'%d 0 obj\n' % i + o + b'\nendobj\n'
    xref = len(out)
    out += b'xref\n0 %d\n0000000000 65535 f \n' % (len(objs) + 1)
    out += b''.join(b'%010d 00000 n \n' % off for off in offsets)
    out += b'trailer\n<< /Size %d /Root 1 0 R >>\nstartxref\n%d\n%%%%EOF\n' % (len(objs) + 1, xref)
    with open(path, 'wb') as fh:
        fh.write(out)


def ymd(d):
    return d.replace('-', '')


def workdays(start, end):
    import datetime as dt
    a, b = dt.date.fromisoformat(start), dt.date.fromisoformat(end)
    return sum(1 for i in range((b - a).days + 1) if (a + dt.timedelta(i)).weekday() < 5)


# ---------------------------------------------------------------- data -----
COMPANIES = [
    # name, type (6 Internal, 2 Vendor, 4 Consultant, 5 Government), phone, email, city, license, description
    ('Oak Hollow Builders', 6, '317-555-0110', 'office@oakhollow.example', 'Noblesville', 'GC-48213',
     'General contractor managing the Cedar Lane home build.'),
    ('Brightline Electric', 2, '317-555-0121', 'service@brightline.example', 'Carmel', 'EL-22917',
     'Licensed residential electrical contractor.'),
    ('ClearFlow Plumbing', 2, '317-555-0132', 'dispatch@clearflow.example', 'Fishers', 'PL-30554',
     'Residential plumbing and water heater installation.'),
    ('Summit Heating & Air', 2, '317-555-0143', 'info@summithvac.example', 'Westfield', 'HV-11870',
     'HVAC design and installation.'),
    ('Nair Design Studio', 4, '317-555-0154', 'studio@nairdesign.example', 'Indianapolis', 'AR-07731',
     'Residential architecture.'),
    ('Riverbend County Building Dept.', 5, '317-555-0165', 'permits@riverbendcounty.example', 'Riverbend', '',
     'Permitting and inspections.'),
]

# username, first, last, company, department, job title, phone, role ('' = no role -> inactive)
USERS = [
    ('dwhitfield', 'Dana', 'Whitfield', 'Oak Hollow Builders', 'Site Operations', 'General Contractor',
     '317-555-0111', 'Project worker', 'Project lead. Runs the weekly site meeting (Tuesdays, 7:30 am).'),
    ('mlee', 'Marcus', 'Lee', 'Brightline Electric', '', 'Master Electrician', '317-555-0122',
     'Project worker', 'Service panel, rough-in and finish electrical.'),
    ('rdelgado', 'Rosa', 'Delgado', 'ClearFlow Plumbing', '', 'Licensed Plumber', '317-555-0133',
     'Project worker', 'Rough-in plumbing, water heater and fixture set.'),
    ('pnair', 'Priya', 'Nair', 'Nair Design Studio', '', 'Architect', '317-555-0155',
     'Project worker', 'Author of the architectural plan set (Rev C).'),
    ('tbrennan', 'Tom', 'Brennan', 'Summit Heating & Air', '', 'HVAC Technician', '317-555-0144',
     '', 'Heat pump and ductwork install; Manual J load calc on file.'),
    ('lortega', 'Luis', 'Ortega', 'Oak Hollow Builders', 'Site Operations', 'Excavation & Concrete Foreman',
     '317-555-0117', '', 'Excavation, footings and foundation pour.'),
]

CONTACTS = [  # contacts who are not system users
    ('Gail', 'Porter', 'Riverbend County Building Dept.', 'Building Inspector', '317-555-0166',
     'gporter@riverbendcounty.example', 'Schedule inspections 48 hours in advance.'),
    ('Ben', 'Hartley', 'Oak Hollow Builders', 'Estimator', '317-555-0118',
     'ben.hartley@oakhollow.example', 'Material takeoffs and supplier quotes.'),
]

RESOURCES = [('EXC-01', 'Mini excavator (rental)', 'Equipment', 100),
             ('DMP-20', '20-yard dumpster', 'Equipment', 100),
             ('TRL-01', 'Job-site office trailer', 'Venue', 100),
             ('LSR-02', 'Rotary laser level', 'Tool', 50)]

# name, start, end, deps (1-based), pct, priority, assignees {username: %}, resources, description
TASKS = [
    ('Finalize architectural plans', '2026-09-01', '2026-09-18', [], 100, 1, {'pnair': 100, 'admin': 25}, [],
     'Sign off on Rev C plan set with Nair Design Studio.'),
    ('Obtain building permit', '2026-09-21', '2026-10-09', [1], 100, 1, {'dwhitfield': 100, 'admin': 25}, [],
     'Submit plans to Riverbend County; respond to plan-review comments.'),
    ('Site survey, staking & clearing', '2026-09-21', '2026-10-02', [1], 100, 0, {'dwhitfield': 100}, ['DMP-20'],
     'Boundary survey, stake footprint, clear and grade lot.'),
    ('Excavation & footings', '2026-10-12', '2026-10-30', [2, 3], 45, 1, {'dwhitfield': 100}, ['EXC-01', 'LSR-02'],
     'Dig basement, set forms, pour footings. Footing inspection required.'),
    ('Foundation walls & slab', '2026-11-02', '2026-11-20', [4], 0, 1, {'dwhitfield': 100}, [],
     'Poured walls, waterproofing, drain tile, basement slab.'),
    ('Framing & sheathing', '2026-11-23', '2027-01-08', [5], 0, 1, {'dwhitfield': 100}, ['TRL-01'],
     'Floor system, walls, trusses, sheathing and house wrap.'),
    ('Roofing', '2027-01-11', '2027-01-29', [6], 0, 0, {'dwhitfield': 50}, [],
     'Underlayment, architectural shingles, flashing, gutters.'),
    ('Windows & exterior doors', '2027-01-11', '2027-01-22', [6], 0, 0, {'dwhitfield': 50}, [],
     'Install and flash windows, entry and patio doors.'),
    ('Rough-in plumbing', '2027-02-01', '2027-02-12', [7], 0, 0, {'rdelgado': 100}, [],
     'Supply, drain/waste/vent, tub sets.'),
    ('Rough-in electrical', '2027-02-01', '2027-02-19', [7], 0, 0, {'mlee': 100}, [],
     '200A panel, boxes, home runs, low-voltage.'),
    ('HVAC ductwork & equipment', '2027-02-08', '2027-02-26', [7], 0, 0, {'dwhitfield': 25}, [],
     'Heat pump, air handler and duct runs (Summit Heating & Air).'),
    ('Rough-in inspection', '2027-03-01', '2027-03-05', [9, 10, 11], 0, 1,
     {'dwhitfield': 100, 'mlee': 25, 'rdelgado': 25}, [],
     'Framing, plumbing, electrical and mechanical rough-in inspections.'),
    ('Insulation & drywall', '2027-03-08', '2027-04-09', [12], 0, 0, {'dwhitfield': 100}, ['DMP-20'],
     'Insulation inspection, hang, tape, finish and prime.'),
    ('Cabinets, flooring & paint', '2027-04-12', '2027-05-21', [13], 0, 0, {'dwhitfield': 100, 'admin': 50}, [],
     'Kitchen and bath cabinets, LVP and tile, interior paint, trim.'),
    ('Siding, driveway & landscaping', '2027-04-19', '2027-05-28', [8], 0, -1, {'dwhitfield': 50, 'admin': 25}, [],
     'Fiber-cement siding, concrete driveway, final grade and sod.'),
    ('Final inspection & certificate of occupancy', '2027-06-07', '2027-06-18', [14, 15], 0, 1,
     {'dwhitfield': 100, 'admin': 100}, [], 'Final walkthrough with inspector; obtain CO; owner punch list.'),
]

# task#, user, date, hours, percent, summary
TASK_LOGS = [
    (1, 'pnair', '2026-09-11', 12, 80, 'Rev B markups incorporated; window schedule updated.'),
    (1, 'pnair', '2026-09-18', 6, 100, 'Rev C issued for permit.'),
    (2, 'dwhitfield', '2026-09-24', 3, 40, 'Permit application and plan set submitted.'),
    (2, 'dwhitfield', '2026-10-09', 2, 100, 'Permit BP-2026-0412 issued and posted on site.'),
    (3, 'dwhitfield', '2026-10-02', 18, 100, 'Lot cleared, footprint staked and checked against survey.'),
    (4, 'dwhitfield', '2026-10-14', 16, 25, 'Basement excavation complete; spoils stockpiled on east side.'),
    (4, 'dwhitfield', '2026-10-16', 10, 45, 'Footing forms set; rebar delivered.'),
]

LINKS = [('The Home Depot', 'https://www.homedepot.com', 'Lumber, fasteners, tools and appliance orders.'),
         ("Lowe's", 'https://www.lowes.com', 'Windows, doors and plumbing fixtures.'),
         ('Menards', 'https://www.menards.com', 'Bulk lumber and roofing quotes.'),
         ('Ace Hardware', 'https://www.acehardware.com', 'Local store for small parts and keys.'),
         ('Habitat for Humanity ReStore', 'https://www.habitat.org/restores', 'Salvaged cabinets, doors and fixtures.')]

# name, start date, start time, end time, type (1 appt, 2 meeting, 3 all day), attendees, description
EVENTS = [
    ('Permit application submitted', '2026-09-24', '090000', '100000', 1, ['admin', 'dwhitfield'],
     'Drop off plan set and application at Riverbend County Building Dept.'),
    ('Building permit approved - pick up', '2026-10-09', '140000', '150000', 1, ['dwhitfield'],
     'Permit BP-2026-0412 ready for pickup; post on site.'),
    ('Groundbreaking ceremony', '2026-10-13', '100000', '113000', 2, ['admin', 'dwhitfield', 'pnair'],
     'Family, Oak Hollow Builders crew and neighbors. Shovels provided.'),
    ('Weekly site meeting', '2026-10-13', '073000', '083000', 2, ['admin', 'dwhitfield'],
     'Schedule, deliveries and safety review at the job-site trailer.'),
    ('Concrete delivery - footings', '2026-10-29', '070000', '090000', 1, ['dwhitfield'],
     '18 yd ready-mix; pump truck on site 6:45 am.'),
    ('Footing inspection', '2026-10-28', '090000', '100000', 1, ['admin', 'dwhitfield'],
     'County inspector to check footings before the pour.'),
    ('Framing walkthrough with owners', '2027-01-08', '150000', '163000', 2, ['admin', 'dwhitfield', 'mlee'],
     'Walk the framed house; confirm outlet and blocking locations.'),
    ('Rough-in inspection', '2027-03-02', '090000', '120000', 1, ['dwhitfield', 'mlee', 'rdelgado'],
     'Plumbing, electrical and mechanical rough-in inspections.'),
    ('Final inspection & CO', '2027-06-15', '090000', '110000', 1, ['admin', 'dwhitfield'],
     'Final inspection; certificate of occupancy issued on pass.'),
    ('Move-in day', '2027-06-26', '080000', '170000', 3, ['admin'], 'Moving truck reserved.'),
]

FOLDERS = [('Plans & Drawings', 'Architectural, structural and trade drawings'),
           ('Permits & Inspections', 'Permit, inspection reports and certificates'),
           ('Contracts & Budget', 'Agreements, budget and draw schedule')]

FILES = [('Plans & Drawings', 1, 'Architectural_Plans_RevC.pdf', 'Architectural plan set, Rev C.'),
         ('Plans & Drawings', 1, 'Site_Plan_and_Survey.pdf', 'Boundary survey and site plan with setbacks.'),
         ('Plans & Drawings', 10, 'Electrical_Layout.pdf', 'Outlet, lighting and panel schedule.'),
         ('Plans & Drawings', 9, 'Plumbing_Riser_Diagram.pdf', 'Supply and DWV riser diagram.'),
         ('Plans & Drawings', 11, 'HVAC_Manual_J_Load_Calc.pdf', 'Heating and cooling load calculation.'),
         ('Permits & Inspections', 2, 'Building_Permit_BP-2026-0412.pdf', 'Issued residential building permit.'),
         ('Permits & Inspections', 4, 'Foundation_Engineering_Letter.pdf', 'Engineer letter for footings and walls.'),
         ('Contracts & Budget', 0, 'Construction_Contract_OakHollow.pdf', 'General contractor agreement.'),
         ('Contracts & Budget', 0, 'Budget_and_Draw_Schedule.pdf', 'Construction budget and lender draw schedule.')]

FORUM_TOPICS = [
    ('dwhitfield', 'Footing pour moved to Thursday',
     'Rain Wednesday, so the footing pour moves to Thursday 10/29. Pump truck confirmed for 6:45 am.',
     [('admin', 'Thanks Dana. We will stop by after the pour.'),
      ('pnair', 'Please send photos of the rebar at the garage step-down before the pour.')]),
    ('mlee', 'Panel location in the basement',
     'Plans show the 200A panel on the north wall. OK to move it 4 ft east so it clears the sump?',
     [('pnair', 'Fine with me; I will update sheet E-1.'), ('dwhitfield', 'Approved. Note it on the as-builts.')]),
]


def main():
    if '--install' in sys.argv:
        install()

    admin = W2P('admin', ADMIN_PASS).login()

    step('system configuration and modules')
    admin.submit('m=system&a=systemconfig', {
        'w2Pcfg[company_name]': 'Oak Hollow Builders',
        'w2Pcfg[admin_email]': 'admin@oakhollow.example',
        'w2Pcfg[activate_external_user_creation]': 'true',
        # The timezone list has no blank/UTC entry, so an untouched save would
        # silently store its first option (Africa/Algiers). Set it explicitly.
        'w2Pcfg[system_timezone]': TIMEZONE,
    }, form_name='cfgFrm')
    # Times are stored using the system timezone but displayed using each user's
    # TIMEZONE preference, which starts out empty (= UTC). Set the default
    # preferences (copied to users created later) and the admin's own.
    for query in ('m=system&a=addeditpref', 'm=system&a=addeditpref&user_id=1'):
        admin.submit(query, {'pref_name[TIMEZONE]': TIMEZONE}, form_name='changeuser')
    for mod in ('forums', 'departments', 'resources'):
        mid = sql_id("select mod_id from modules where mod_directory='%s' and mod_active=0" % mod)
        if mid:
            # use the page's own link: it carries the session's form token (CSRF)
            link = re.search(r'href="\?(m=system&u=modules&a=domodsql&__nonce=[0-9a-f]+&mod_id=%s&cmd=toggle&?)"' % mid,
                             admin.get('m=system&u=modules').text).group(1)
            admin.get(link)

    # A fresh install gives the 'Project worker' role only the login permission,
    # so grant it access/add/edit/view (not delete) on the non-admin modules.
    role_id = sql_id("select id from gacl_aro_groups where value='normal'")
    if sql_id("select count(*) from gacl_aro_groups_map where group_id=%s" % role_id) == '0':
        admin.submit('m=system&u=roles&a=viewrole&role_id=%s' % role_id, {
            'permission_module': 'grp,13', 'permission_access': '1', 'permission_item': '0',
            'permission_type[]': ['11', '12', '13', '14']}, form_name='frmPerms')  # access, view, add, edit

    step('custom fields')
    admin.submit('m=system&u=customfields&a=addedit&module=1', dict(
        field_name='license_number', field_description='Contractor License #', field_htmltype='textinput',
        field_published='1', field_order='1'), form_name='addEditForm')

    step('companies, departments, billing codes')
    for name, ctype, phone, email, city, lic, desc in COMPANIES:
        values = dict(company_name=name, company_type=ctype, company_phone1=phone, company_email=email,
                      company_city=city, company_state='IN', company_country='US', company_owner='1',
                      company_description=desc, company_primary_url=email.split('@')[1])
        _, page = admin.form_defaults('m=companies&a=addedit')
        field = re.search(r'name="([^"]*license_number[^"]*)"', page)
        if field and lic:
            values[field.group(1)] = lic
        if name == 'Oak Hollow Builders':
            values.update(company_address1='220 Mill Street', company_zip='46060')
        admin.submit('m=companies&a=addedit', values)
    oak = sql_id("select company_id from companies where company_name='Oak Hollow Builders'")
    for dname, desc in (('Site Operations', 'Field crews, site supervision and scheduling.'),
                        ('Estimating & Purchasing', 'Takeoffs, supplier quotes and purchase orders.')):
        admin.submit('m=departments&a=addedit&company_id=%s' % oak, dict(
            dept_name=dname, dept_desc=desc, dept_company=oak, dept_owner='1', dept_city='Noblesville',
            dept_state='IN', dept_country='US', dept_phone='317-555-0110'))
    for code, value, desc in (('SITE', 65, 'Site labor'), ('SUPV', 85, 'Supervision'),
                              ('EQUIP', 120, 'Heavy equipment')):
        admin.submit('m=system&a=billingcode', dict(billingcode_company=oak, billingcode_name=code,
                                                    billingcode_value=value, billingcode_desc=desc,
                                                    billingcode_category='1'), form_name='frmAddcode')

    step('homeowner (admin contact)')
    admin.submit('m=contacts&a=addedit&contact_id=1', dict(
        contact_first_name='Jordan', contact_last_name='Avery', contact_display_name='Jordan Avery',
        contact_job='Homeowner', contact_email='jordan.avery@example.com',
        contact_phone='317-555-0190', contact_city='Noblesville', contact_state='IN', contact_country='US'),
        form_name='changecontact')

    step('users')
    for uname, first, last, company, dept, job, phone, role, notes in USERS:
        _, page = admin.form_defaults('m=users&a=addedit')
        role_id = re.search(r'<option value="(\d+)"[^>]*>%s</option>' % re.escape(role), page) if role else None
        company_id = sql_id("select company_id from companies where company_name='%s'" % company.replace("'", "''"))
        dept_id = sql_id("select dept_id from departments where dept_name='%s'" % dept) if dept else ''
        email = '%s@%s' % (first.lower(), {'Oak Hollow Builders': 'oakhollow.example'}.get(
            company, company.split()[0].lower() + '.example'))
        admin.submit('m=users&a=addedit', dict(
            user_username=uname, user_password=USER_PASS, password_check=USER_PASS, contact_first_name=first,
            contact_last_name=last, contact_company=company_id, contact_department=dept_id or '',
            user_role=role_id.group(1) if role_id else '0', contact_email=email, send_user_mail='0'))
        cid = sql_id("select user_contact from users where user_username='%s'" % uname)
        admin.submit('m=contacts&a=addedit&contact_id=%s' % cid, dict(
            contact_job=job, contact_phone=phone, contact_notes=notes,
            contact_city='Noblesville', contact_state='IN', contact_country='US'), form_name='changecontact')

    step('other contacts')
    for first, last, company, job, phone, email, notes in CONTACTS:
        company_id = sql_id("select company_id from companies where company_name='%s'" % company)
        admin.submit('m=contacts&a=addedit', dict(
            contact_first_name=first, contact_last_name=last, contact_display_name='%s %s' % (first, last),
            contact_company=company_id, contact_job=job, contact_phone=phone,
            contact_email=email, contact_notes=notes, contact_city='Noblesville', contact_state='IN',
            contact_country='US'), form_name='changecontact')

    uid = {u: sql_id("select user_id from users where user_username='%s'" % u)
           for u in ['admin'] + [u[0] for u in USERS]}

    step('resources')
    for key, name, rtype, alloc in RESOURCES:
        _, page = admin.form_defaults('m=resources&a=addedit')
        tid = re.search(r'<option value="(\d+)"[^>]*>%s</option>' % rtype, page).group(1)
        admin.submit('m=resources&a=addedit', dict(resource_key=key, resource_name=name, resource_type=tid,
                                                   resource_max_allocation=alloc,
                                                   resource_description='Booked for the Cedar Lane build.'))
    rid = {k: sql_id("select resource_id from resources where resource_key='%s'" % k) for k, *_ in RESOURCES}

    step('project')
    contact_ids = ','.join(r for r in [sql_id("select contact_id from contacts where contact_email='%s'" % e)
                                       for e in ['gporter@riverbendcounty.example', 'ben.hartley@oakhollow.example']]
                           + [sql_id("select user_contact from users where user_username='%s'" % u[0])
                              for u in USERS] if r)
    admin.submit('m=projects&a=addedit', dict(
        project_name='Build Family Home - 1420 Cedar Lane', project_short_name='CedarLane',
        project_company=oak, project_owner=uid['dwhitfield'], project_status='3', project_type='2',
        project_priority='1', project_start_date='20260901', start_date='01/Sep/2026',
        project_end_date='20270625', end_date='25/Jun/2027', project_location='1420 Cedar Lane, Noblesville, IN',
        project_color_identifier='8FBC5A', project_contacts=contact_ids, project_url='',
        project_description='New construction of a 2,400 sq ft, 4-bed / 3-bath single-family home. '
                            'Oak Hollow Builders is the general contractor.'))
    pid = sql_id("select project_id from projects where project_short_name='CedarLane'")

    step('tasks')
    task_ids = []
    for name, start, end, deps, pct, pri, assign, res, desc in TASKS:
        admin.submit('m=tasks&a=addedit&task_project=%s' % pid, dict(
            task_name=name, task_project=pid, new_task_project=pid, task_owner=uid['dwhitfield'],
            task_access='0', task_priority=pri, task_status='0', task_percent_complete=pct,
            task_description=desc, task_start_date=ymd(start) + '0800', task_end_date=ymd(end) + '1700',
            start_hour='08', start_minute='00', end_hour='17', end_minute='00',
            task_duration=workdays(start, end), task_duration_type='24',
            hdependencies=','.join(task_ids[d - 1] for d in deps), task_dynamic='31',
            hassign=','.join(uid[u] for u in assign),
            hperc_assign=''.join('%s=%s;' % (uid[u], p) for u, p in assign.items()),
            hresource_assign=''.join('%s=100;' % rid[r] for r in res), task_notify='0'))
        task_ids.append(sql_id("select max(task_id) from tasks where task_name='%s'" % name.replace("'", "''")))

    step('task logs, forum posts (as the users themselves)')
    sessions = {'admin': admin}
    for u in ('dwhitfield', 'mlee', 'pnair', 'rdelgado'):
        sessions[u] = W2P(u, USER_PASS).login()
    for tnum, user, date, hours, pct, summary in TASK_LOGS:
        tid = task_ids[tnum - 1]
        sessions[user].submit('m=tasks&a=view&task_id=%s&tab=1' % tid, dict(
            task_log_date=ymd(date), log_date=date, task_log_costcode='1', task_log_hours=hours, task_percent_complete=pct,
            task_log_name=summary, task_log_description=summary), must_have='task_log_hours')
    admin.submit('m=forums&a=addedit', dict(
        forum_name='Cedar Lane Build - Site Updates', forum_project=pid, forum_owner=uid['dwhitfield'],
        forum_moderated=uid['dwhitfield'], forum_description='Day-to-day questions and decisions for the build.'))
    admin.submit('m=forums&a=addedit', dict(
        forum_name='Owner Selections', forum_project=pid, forum_owner=uid['admin'], forum_moderated=uid['admin'],
        forum_description='Finishes, fixtures and color choices.'))
    fid = sql_id("select forum_id from forums where forum_name like 'Cedar Lane%'")
    for author, title, body, replies in FORUM_TOPICS:
        sessions[author].submit('m=forums&a=viewer&forum_id=%s&post_message=1' % fid, dict(
            message_title=title, message_body=body))
        mid = sql_id("select message_id from forum_messages where message_title='%s'" % title)
        for who, text in replies:
            sessions[who].submit('m=forums&a=viewer&forum_id=%s&message_parent=%s&post_message=1' % (fid, mid),
                                 dict(message_title='Re: ' + title, message_body=text, message_parent=mid))

    step('links')
    for name, url, desc in LINKS:
        admin.submit('m=links&a=addedit', dict(link_name=name, link_url=url, link_description=desc,
                                               link_category='0', link_project=pid, link_owner='1'))

    step('events')
    for name, date, st, et, etype, who, desc in EVENTS:
        admin.submit('m=events&a=addedit', dict(
            event_name=name, event_type=etype, event_project=pid, event_owner='1', event_start_datetime=ymd(date),
            start_time=st, event_end_datetime=ymd(date), end_time=et, event_description=desc,
            event_assigned=','.join(uid[u] for u in who), event_recurs='0', event_times_recuring='1'))

    step('file folders and placeholder files')
    for fname, desc in FOLDERS:
        admin.submit('m=files&a=addedit_folder', dict(file_folder_name=fname, file_folder_description=desc,
                                                      file_folder_parent='0'), form_name='folderFrm')
    os.makedirs(os.path.join(HERE, 'placeholder-files'), exist_ok=True)
    for folder, tnum, fname, desc in FILES:
        folder_id = sql_id("select file_folder_id from file_folders where file_folder_name='%s'" % folder)
        path = os.path.join(HERE, 'placeholder-files', fname)
        placeholder_pdf(path, fname[:-4].replace('_', ' '),
                        ['Build Family Home - 1420 Cedar Lane', desc,
                         'PLACEHOLDER DOCUMENT - sample data for web2project screenshots.'])
        with open(path, 'rb') as fh:
            admin.submit('m=files&a=addedit&folder=%s&project_id=%s' % (folder_id, pid), dict(
                file_project=pid, file_folder=folder_id, file_category='1', file_description=desc,
                file_task=task_ids[tnum - 1] if tnum else '0'),
                files={'formfile': (fname, fh, 'application/pdf')})

    step('budgets (entered with Display Budget Information on, then switched off again)')
    set_budget_display(admin, True)
    cats = dict(re.findall(r'<option value="(\d+)"[^>]*>([^<]+)</option>',
                           re.search(r'name="budget_category".*?</select>',
                                     admin.get('m=system&a=budgeting').text, re.S).group(0)))
    cat_id = {name: cid for cid, name in cats.items()}
    for cat, amount in (('Labor', 210000), ('Licensing', 6500), ('Administrative', 14000)):
        admin.submit('m=system&a=budgeting', dict(
            budget_company=oak, budget_start_date='20260901', start_date='01/Sep/2026',
            budget_end_date='20270630', end_date='30/Jun/2027', budget_amount=amount,
            budget_category=cat_id[cat]), form_name='frmAddcode')
    admin.submit('m=projects&a=addedit&project_id=%s' % pid, {
        'budget_%s' % cat_id['Labor']: 320000, 'budget_%s' % cat_id['Licensing']: 6500,
        'budget_%s' % cat_id['Administrative']: 15000, 'budget_%s' % cat_id['Travel']: 2500})
    set_budget_display(admin, False)

    print('done: project %s, %d tasks' % (pid, len(task_ids)))


def set_budget_display(session, on):
    """Toggle System Configuration -> Display Budget Information."""
    session.submit('m=system&a=systemconfig', {'w2Pcfg[budget_info_display]': 'true' if on else None},
                   form_name='cfgFrm')


if __name__ == '__main__':
    main()

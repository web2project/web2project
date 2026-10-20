"""Tiny HTTP client that drives web2project the way a browser does.

Every record is created by loading the real add/edit form, keeping its
defaults and hidden fields (including the CSRF __nonce) and posting it back,
so all data goes through the application's normal validation and store().
"""
import html
import os
import re
import subprocess

import requests

BASE_URL = os.environ.get('W2P_URL', 'http://127.0.0.1:8080')
INDEX = BASE_URL + '/index.php'


class W2P:
    def __init__(self, username='admin', password=None):
        self.s = requests.Session()
        self.username = username
        self.password = password or os.environ.get('W2P_ADMIN_PASS', 'passw0rd')

    # -- session -----------------------------------------------------------
    def login(self):
        self.s.get(INDEX)
        r = self.s.post(INDEX, data=dict(login='login', username=self.username,
                                         password=self.password, redirect='', lostpass='0'))
        if 'logout' not in r.text.lower():
            raise RuntimeError('login failed for %s' % self.username)
        return self

    def get(self, query):
        return self.s.get(INDEX + '?' + query)

    # -- forms -------------------------------------------------------------
    def _find_form(self, page, form_name, must_have=None):
        for m in re.finditer(r'<form([^>]*name="%s"[^>]*)>(.*?)</form>' % re.escape(form_name), page, re.S):
            if must_have is None or 'name="%s"' % must_have in m.group(2):
                return m
        return None

    def form_defaults(self, query, form_name='editFrm', must_have=None):
        page = self.get(query).text
        m = self._find_form(page, form_name, must_have)
        body = m.group(2) if m else page
        data = {}
        for mm in re.finditer(r'<input([^>]*)>', body):
            attrs = mm.group(1)
            name = re.search(r'''name=["']([^"']*)["']''', attrs)
            if not name or "'" in name.group(1) or '+' in name.group(1):
                continue
            itype = (re.search(r'''type=["']([^"']*)["']''', attrs) or [None, 'text'])[1].lower()
            value = re.search(r'''value=["']([^"']*)["']''', attrs)
            value = html.unescape(value.group(1)) if value else ''
            if itype in ('checkbox', 'radio') and 'checked' not in attrs:
                continue
            if itype in ('submit', 'button', 'file', 'image', 'reset'):
                continue
            data[name.group(1)] = value
        for mm in re.finditer(r'<select([^>]*)>(.*?)</select>', body, re.S):
            name = re.search(r'name="([^"]*)"', mm.group(1))
            if not name or 'multiple' in mm.group(1):
                continue
            sel = re.search(r'<option[^>]*value="([^"]*)"[^>]*selected', mm.group(2))
            first = re.search(r'<option[^>]*value="([^"]*)"', mm.group(2))  # what a browser submits
            data[name.group(1)] = sel.group(1) if sel else (first.group(1) if first else '')
        for mm in re.finditer(r'<textarea([^>]*)>(.*?)</textarea>', body, re.S):
            name = re.search(r'name="([^"]*)"', mm.group(1))
            if name:
                data[name.group(1)] = html.unescape(mm.group(2))
        return data, page

    def submit(self, query, values, form_name='editFrm', files=None, must_have=None):
        data, page = self.form_defaults(query, form_name, must_have)
        for k, v in values.items():
            if v is None:  # None = leave the field out, e.g. an unticked checkbox
                data.pop(k, None)
            else:
                data[k] = v if isinstance(v, list) else str(v)
        form = self._find_form(page, form_name, must_have)
        action = re.search(r'action="([^"]*)"', form.group(1)) if form else None
        action = html.unescape(action.group(1)) if action else '?' + query
        action = action.replace('./index.php', '').replace('index.php', '')
        r = self.s.post(INDEX + action, data=data, files=files)
        notes = messages(r.text)
        if any(w in ' '.join(notes).lower() for w in ('error', 'failed', 'invalid', 'not ')):
            print('   !!', query, notes)
        return r


def messages(page):
    found = re.findall(r'class="[^"]*(?:message|error|alert)[^"]*"[^>]*>(.*?)</', page, re.S)
    return [re.sub('<[^>]+>', '', x).strip() for x in found if x.strip()][:3]


def sql(query, db=os.environ.get('W2P_DB', 'w2p')):
    """Read-only lookups (ids by name). Writes always go through the UI."""
    out = subprocess.check_output(['mysql', db, '-N', '-B', '-e', query]).decode()
    return [line.split('\t') for line in out.strip().splitlines() if line]


def sql_id(query):
    rows = sql(query)
    return rows[0][0] if rows else None

#!/usr/bin/env python3
"""Capture the installer screens from an unconfigured copy of web2project.

    # a second copy with no includes/config.php, served on its own port,
    # and an empty database for it:
    cp -a /path/to/web2project /tmp/w2p_inst && rm -f /tmp/w2p_inst/includes/config.php
    mysql -e "CREATE DATABASE w2p_inst; GRANT ALL ON w2p_inst.* TO 'w2p'@'localhost'"
    php -S 127.0.0.1:8081 -t /tmp/w2p_inst &
    python3 installer_screenshots.py --out ./shots

Writes installer/welcome.png, installer/requirements.png, installer/database.png
and installer/finished.png (+ thumb-*.png). The last step really installs into
the w2p_inst database.
"""
import argparse
import os

from playwright.sync_api import sync_playwright

from screenshots import VIEWPORT, thumbnail

URL = os.environ.get('W2P_INSTALL_URL', 'http://127.0.0.1:8081') + '/install/index.php'


def shot(pg, out, name):
    pg.evaluate('() => { document.activeElement && document.activeElement.blur(); window.scrollTo(0, 0); }')
    pg.mouse.move(VIEWPORT['width'] - 5, VIEWPORT['height'] - 5)
    dest = os.path.join(out, 'installer', name)
    os.makedirs(os.path.dirname(dest), exist_ok=True)
    pg.screenshot(path=dest)
    thumbnail(dest, os.path.join(out, 'installer', 'thumb-' + name))
    print('captured installer/' + name)


def main(out):
    with sync_playwright() as p:
        br = p.chromium.launch()
        pg = br.new_page(viewport=VIEWPORT)
        pg.goto(URL)
        shot(pg, out, 'welcome.png')
        with pg.expect_navigation():
            pg.click('input[type=submit]')
        shot(pg, out, 'requirements.png')
        with pg.expect_navigation():
            pg.click('input[type=submit]')
        pg.fill('input[name=dbhost]', '127.0.0.1')
        pg.fill('input[name=dbname]', os.environ.get('W2P_INSTALL_DB', 'w2p_inst'))
        pg.fill('input[name=dbuser]', os.environ.get('W2P_DB_USER', 'w2p'))
        pg.fill('input[name=dbpass]', os.environ.get('W2P_DB_PASS', 'w2p'))
        pg.fill('input[name=adminpass]', 'passw0rd')
        shot(pg, out, 'database.png')
        with pg.expect_navigation(timeout=120000):
            pg.click('input[name=do_db_cfg]')
        shot(pg, out, 'finished.png')
        br.close()


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('--out', default='shots')
    main(ap.parse_args().out)

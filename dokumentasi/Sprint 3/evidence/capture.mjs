// Capture responsive screenshots for Sprint 3 evidence.
// Usage: node capture.mjs
import { spawn } from 'node:child_process';
import { writeFile, mkdir } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';

const CHROME = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE = 'http://127.0.0.1:8099';
const OUT = path.resolve('dokumentasi/Sprint 3/evidence/screenshots');
const PORT = 9223;
const PROFILE = path.join(tmpdir(), 'jelajahku-s3-chrome');

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function waitForDebugger() {
  for (let i = 0; i < 50; i++) {
    try {
      const res = await fetch(`http://127.0.0.1:${PORT}/json/version`);
      if (res.ok) return;
    } catch {}
    await sleep(200);
  }
  throw new Error('Chrome DevTools tidak siap');
}

class CDP {
  constructor(ws) {
    this.ws = ws;
    this.id = 0;
    this.pending = new Map();
    ws.addEventListener('message', (event) => {
      const msg = JSON.parse(event.data);
      if (msg.id && this.pending.has(msg.id)) {
        const { resolve, reject } = this.pending.get(msg.id);
        this.pending.delete(msg.id);
        msg.error ? reject(new Error(JSON.stringify(msg.error))) : resolve(msg.result);
      }
    });
  }

  send(method, params = {}) {
    const id = ++this.id;
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      this.ws.send(JSON.stringify({ id, method, params }));
    });
  }
}

async function main() {
  await mkdir(OUT, { recursive: true });

  const chrome = spawn(CHROME, [
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-first-run',
    '--no-default-browser-check',
    '--force-device-scale-factor=1',
    '--remote-allow-origins=*',
    `--remote-debugging-port=${PORT}`,
    `--user-data-dir=${PROFILE}`,
    'about:blank',
  ], { stdio: 'ignore' });

  try {
    await waitForDebugger();

    const target = await (await fetch(`http://127.0.0.1:${PORT}/json/new?about:blank`, { method: 'PUT' })).json();
    const ws = new WebSocket(target.webSocketDebuggerUrl);
    await new Promise((resolve, reject) => {
      ws.addEventListener('open', resolve, { once: true });
      ws.addEventListener('error', reject, { once: true });
    });

    const cdp = new CDP(ws);
    await cdp.send('Page.enable');
    await cdp.send('Runtime.enable');

    async function setViewport(width, height, mobile) {
      await cdp.send('Emulation.setDeviceMetricsOverride', {
        width, height, deviceScaleFactor: 1, mobile,
        screenWidth: width, screenHeight: height,
      });
      await cdp.send('Emulation.setTouchEmulationEnabled', { enabled: mobile });
    }

    async function goto(url) {
      await cdp.send('Page.navigate', { url });
      for (let i = 0; i < 80; i++) {
        const { result } = await cdp.send('Runtime.evaluate', {
          expression: 'document.readyState',
          returnByValue: true,
        });
        if (result.value === 'complete') break;
        await sleep(150);
      }
      await sleep(1800); // beri waktu CDN Tailwind/Alpine dan gambar termuat
    }

    async function shot(name) {
      const { data } = await cdp.send('Page.captureScreenshot', {
        format: 'png', fromSurface: true, captureBeyondViewport: false,
      });
      await writeFile(path.join(OUT, `${name}.png`), Buffer.from(data, 'base64'));
      console.log('saved', name);
    }

    async function capturePair(name, url) {
      await setViewport(390, 844, true);
      await goto(url);
      await shot(`${name}-mobile-390x844`);

      await setViewport(1440, 900, false);
      await goto(url);
      await shot(`${name}-desktop-1440x900`);
    }

    async function captureOne(name, url, width, height, mobile) {
      await setViewport(width, height, mobile);
      await goto(url);
      await shot(name);
    }

    // Halaman publik (sebelum login admin).
    await capturePair('beranda', `${BASE}/`);
    await captureOne('beranda-tablet-768x1024', `${BASE}/`, 768, 1024, true);
    await capturePair('destinasi', `${BASE}/destinasi`);
    await capturePair('masuk', `${BASE}/masuk`);
    await capturePair('admin-login', `${BASE}/admin/login`);
    await capturePair('web-404', `${BASE}/halaman-tidak-ada`);

    // Login admin melalui form asli, lalu tangkap area admin yang terlindungi.
    await setViewport(1440, 900, false);
    await goto(`${BASE}/admin/login`);
    await cdp.send('Runtime.evaluate', {
      expression: `(() => {
        document.querySelector('input[name=email]').value = 'admin@jelajahku.test';
        document.querySelector('input[name=password]').value = 'password';
        document.querySelector('form').submit();
      })()`,
    });
    await sleep(2500);

    await goto(`${BASE}/admin`);
    await shot('admin-dashboard-desktop-1440x900');
    await setViewport(390, 844, true);
    await goto(`${BASE}/admin`);
    await shot('admin-dashboard-mobile-390x844');
    await setViewport(768, 1024, true);
    await goto(`${BASE}/admin`);
    await shot('admin-dashboard-tablet-768x1024');

    await setViewport(1440, 900, false);
    await goto(`${BASE}/admin/booking`);
    await shot('admin-booking-desktop-1440x900');

    ws.close();
  } finally {
    chrome.kill();
  }
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});

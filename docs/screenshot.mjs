import { chromium } from 'playwright-core'
import { mkdirSync } from 'node:fs'

// ─── KONFIGURASI ────────────────────────────────────────────────────────────

const BASE = 'http://127.0.0.1:8000'
const OUT = 'docs/screenshoots'
const BROWSER = 'C:/Program Files/Google/Chrome/Application/chrome.exe'

const LOGIN = {
    path: '/login',
    userField: '#email',
    passField: '#password',
    submit: 'button[type=submit]',
    username: 'admin@wisatapadang.test',
    password: 'password',
}

// 1 fitur = 1 screenshot.
//   guest  : true  → diambil tanpa login
//   map    : true  → tunggu lebih lama (tile Leaflet) & screenshot viewport saja
//   chat   : true  → kirim 1 pertanyaan ke AI Concierge dulu
const PAGES = [
    // ── Publik ──────────────────────────────────────────────────────────
    { name: '01-beranda', path: '/', guest: true },
    { name: '02-explore', path: '/explore', guest: true },
    { name: '03-detail-destinasi', path: '/destinasi/pantai-padang-taplau', guest: true },
    { name: '04-halaman-kategori', path: '/kategori/kuliner', guest: true },
    { name: '05-peta-interaktif', path: '/peta', guest: true, map: true },
    { name: '06-wisata-sekitarku', path: '/sekitar?lat=-0.9508&lng=100.3616&radius=10', guest: true, map: true },
    { name: '07-kuis-preferensi', path: '/kuis', guest: true },
    { name: '08-kuis-hasil', path: '/kuis/hasil?cocok=keluarga&price=gratis_murah&waktu=pagi', guest: true },
    { name: '09-ai-concierge', path: '/asisten', guest: true, chat: true },
    { name: '10-blog', path: '/blog', guest: true },
    { name: '11-blog-detail', path: '/blog/5-pantai-terbaik-di-padang-untuk-berburu-sunset', guest: true },
    { name: '12-tanya-jawab', path: '/tanya-jawab', guest: true },
    { name: '13-tentang', path: '/tentang', guest: true },
    { name: '14-login', path: '/login', guest: true },
    { name: '15-register', path: '/register', guest: true },

    // ── Area user (butuh login) ─────────────────────────────────────────
    { name: '16-dashboard-user', path: '/dashboard' },
    { name: '17-favorit-saya', path: '/favorit' },
    { name: '18-review-saya', path: '/review-saya' },
    { name: '19-profil', path: '/profile' },

    // ── Panel admin ─────────────────────────────────────────────────────
    { name: '20-admin-dashboard', path: '/admin' },
    { name: '21-admin-destinasi', path: '/admin/destinations' },
    { name: '22-admin-destinasi-form', path: '/admin/destinations/create' },
    { name: '23-admin-kategori', path: '/admin/categories' },
    { name: '24-admin-tag', path: '/admin/tags' },
    { name: '25-admin-artikel', path: '/admin/articles' },
    { name: '26-admin-user', path: '/admin/users' },
    { name: '27-admin-moderasi-review', path: '/admin/reviews' },
    { name: '28-admin-tanya-jawab', path: '/admin/questions' },
    { name: '29-admin-ai-concierge', path: '/admin/concierge' },
    { name: '30-admin-pengaturan', path: '/admin/settings' },
]

// Ambil ulang sebagian saja:  node docs/screenshot.mjs concierge
const ONLY = process.argv[2]
const SELECTED = ONLY ? PAGES.filter((p) => p.name.includes(ONLY)) : PAGES

const NEEDS_LOGIN = SELECTED.filter((p) => !p.guest)

// ─── SCRIPT ─────────────────────────────────────────────────────────────────

mkdirSync(OUT, { recursive: true })

const log = (...a) => console.log('  ', ...a)
let ok = 0
const failed = []

const browser = await chromium.launch({ executablePath: BROWSER, headless: true })
const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 2,
})
const page = await context.newPage()

async function settle(ms = 800) {
    await page.waitForLoadState('networkidle').catch(() => {})
    await page.waitForTimeout(ms)
}

/** Scroll sampai bawah lalu balik ke atas — memicu gambar & tile peta termuat. */
async function autoScroll() {
    await page.evaluate(async () => {
        await new Promise((resolve) => {
            let y = 0
            const timer = setInterval(() => {
                window.scrollBy(0, 600)
                y += 600
                if (y >= document.body.scrollHeight) {
                    clearInterval(timer)
                    resolve()
                }
            }, 100)
        })
    })
    await page.waitForTimeout(500)
    await page.evaluate(() => window.scrollTo(0, 0))
    await page.waitForTimeout(400)
}

/** Tunggu semua <img> selesai dimuat (biar tidak ada kotak kosong). */
async function waitImages() {
    await page
        .evaluate(() =>
            Promise.all(
                [...document.images]
                    .filter((img) => !img.complete)
                    .map((img) => new Promise((res) => { img.onload = img.onerror = res })),
            ),
        )
        .catch(() => {})
}

async function capture(p) {
    await page.goto(BASE + p.path, { waitUntil: 'domcontentloaded' })
    await settle(p.map ? 2500 : 900)

    if (p.chat) {
        // Kirim 1 pertanyaan supaya screenshot menampilkan hasil nyata.
        try {
            await page.fill('input[type=text]', 'kuliner enak untuk keluarga')
            await page.click('button[type=submit]')
            // Indikator "mengetik" muncul dulu, lalu hilang saat jawaban tiba.
            await page.waitForSelector('.animate-bounce', { state: 'visible', timeout: 8000 }).catch(() => {})
            await page.waitForSelector('.animate-bounce', { state: 'hidden', timeout: 60000 })
            await page.waitForTimeout(1500)
            // Chat auto-scroll ke bawah; balikkan ke atas supaya pertanyaan
            // dan kalimat jawaban ikut terlihat di screenshot.
            await page.evaluate(() => {
                const el = document.querySelector('[x-ref=thread]')
                if (el) el.scrollTop = 0
            })
            await page.waitForTimeout(600)
        } catch {
            log('   (AI tidak merespons — screenshot kondisi awal)')
        }
    }

    if (!p.map) await autoScroll()
    await waitImages()
    await page.waitForTimeout(400)

    await page.screenshot({ path: `${OUT}/${p.name}.png`, fullPage: !p.map })
    log(`✓ ${p.name}.png`)
    ok++
}

console.log('\nScreenshot publik…')
for (const p of SELECTED.filter((p) => p.guest)) {
    try {
        await capture(p)
    } catch (e) {
        failed.push(`${p.name} — ${e.message.split('\n')[0]}`)
        log(`✗ ${p.name}`)
    }
}

if (NEEDS_LOGIN.length > 0) {
    console.log('\nLogin sebagai admin…')
    await page.goto(BASE + LOGIN.path)
    await page.fill(LOGIN.userField, LOGIN.username)
    await page.fill(LOGIN.passField, LOGIN.password)
    await page.click(LOGIN.submit)
    await page.waitForURL((url) => !url.pathname.endsWith(LOGIN.path), { timeout: 20000 })
    log('login berhasil')

    console.log('\nScreenshot area user & admin…')
    for (const p of NEEDS_LOGIN) {
        try {
            await capture(p)
        } catch (e) {
            failed.push(`${p.name} — ${e.message.split('\n')[0]}`)
            log(`✗ ${p.name}`)
        }
    }
}

await browser.close()

console.log(`\nSelesai: ${ok}/${SELECTED.length} screenshot tersimpan di ${OUT}/`)
if (failed.length) {
    console.log('\nGagal:')
    failed.forEach((f) => console.log('  -', f))
}

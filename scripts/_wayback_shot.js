// One-off helper: screenshot a wayback machine replay URL at 1440x1080.
// Usage: node scripts/_wayback_shot.js <url> <outPath>
const { chromium } = require('playwright');

(async () => {
  const url = process.argv[2];
  const outPath = process.argv[3];
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1080 } });
  try {
    await page.goto(url, { waitUntil: 'networkidle', timeout: 45000 });
  } catch (e) {
    console.error('goto warning:', e.message);
  }
  await page.waitForTimeout(1500);
  await page.screenshot({ path: outPath });
  await browser.close();
  console.log('saved', outPath);
})();

import QRCode from 'qrcode';
import { writeFileSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const outputDir = join(__dirname, '..', 'public');
const url = 'https://rfi.ronakfire.com';

(async () => {
  await QRCode.toFile(join(outputDir, 'pwa-qr.png'), url, {
    type: 'png',
    width: 800,
    margin: 2,
    color: { dark: '#1a56db', light: '#ffffff' },
  });
  console.log('QR code created: public/pwa-qr.png');

  const svg = await QRCode.toString(url, {
    type: 'svg',
    width: 400,
    margin: 2,
    color: { dark: '#1a56db', light: '#ffffff' },
  });
  writeFileSync(join(outputDir, 'pwa-qr.svg'), svg, 'utf-8');
  console.log('QR code created: public/pwa-qr.svg');
})();

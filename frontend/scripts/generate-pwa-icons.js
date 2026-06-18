import sharp from 'sharp';
import { writeFileSync, mkdirSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const outputDir = join(__dirname, '..', 'public', 'pwa-icons');
mkdirSync(outputDir, { recursive: true });

const sizes = [192, 512];
const bgColor = '#1a56db';
const textColor = '#ffffff';

// Create an SVG template for each icon size
async function generateIcon(size) {
  const fontSize = Math.round(size * 0.5);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
    <rect width="${size}" height="${size}" rx="${Math.round(size * 0.2)}" fill="${bgColor}"/>
    <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="${fontSize}" fill="${textColor}">RFI</text>
  </svg>`;

  await sharp(Buffer.from(svg))
    .resize(size, size)
    .png()
    .toFile(join(outputDir, `icon-${size}x${size}.png`));
  console.log(`Created icon-${size}x${size}.png`);
}

// Also create a favicon
async function generateFavicon() {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48">
    <rect width="48" height="48" rx="10" fill="#1a56db"/>
    <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="24" fill="#ffffff">R</text>
  </svg>`;
  
  await sharp(Buffer.from(svg))
    .resize(48, 48)
    .png()
    .toFile(join(outputDir, 'favicon.png'));
  console.log('Created favicon.png');
}

async function main() {
  await Promise.all(sizes.map(generateIcon));
  await generateFavicon();
  console.log('All icons generated!');
}

main().catch(console.error);

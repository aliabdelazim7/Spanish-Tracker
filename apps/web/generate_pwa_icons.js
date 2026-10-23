import sharp from 'sharp';
import fs from 'fs';
import path from 'path';

const publicDir = path.resolve('public');

// Standard icon SVG (512x512)
const standardSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1B4D3E" />
      <stop offset="50%" stop-color="#143c30" />
      <stop offset="100%" stop-color="#0e2a22" />
    </linearGradient>
    <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FCD34D" />
      <stop offset="100%" stop-color="#F59E0B" />
    </linearGradient>
    <linearGradient id="needleNorth" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#EF4444" />
      <stop offset="100%" stop-color="#B91C1C" />
    </linearGradient>
    <linearGradient id="needleSouth" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#F3F4F6" />
      <stop offset="100%" stop-color="#D1D5DB" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="130%" height="130%">
      <feDropShadow dx="0" dy="12" stdDeviation="16" flood-color="#000000" flood-opacity="0.35" />
    </filter>
  </defs>

  <!-- Background rounded squircle -->
  <rect width="512" height="512" rx="112" fill="url(#bgGrad)" />

  <!-- Outer Ring with Compass Dots -->
  <circle cx="256" cy="256" r="180" fill="none" stroke="url(#goldGrad)" stroke-width="6" opacity="0.4" />
  <circle cx="256" cy="256" r="160" fill="none" stroke="#FAF8F5" stroke-width="2" stroke-dasharray="8 12" opacity="0.3" />

  <!-- Compass Cardinal Markers -->
  <circle cx="256" cy="86" r="6" fill="#F59E0B" />
  <circle cx="256" cy="426" r="6" fill="#F59E0B" opacity="0.6" />
  <circle cx="86" cy="256" r="6" fill="#F59E0B" opacity="0.6" />
  <circle cx="426" cy="256" r="6" fill="#F59E0B" opacity="0.6" />

  <!-- Main Compass Needle & Rose -->
  <g filter="url(#shadow)">
    <!-- North Needle (Red - Spanish Passion & Direction) -->
    <path d="M256 100 L290 256 L256 242 L222 256 Z" fill="url(#needleNorth)" />
    <!-- South Needle (Silver Pearl) -->
    <path d="M256 412 L222 256 L256 270 L290 256 Z" fill="url(#needleSouth)" />
    <!-- East / West Subtle Needles -->
    <path d="M412 256 L256 222 L270 256 L256 290 Z" fill="#F59E0B" opacity="0.8" />
    <path d="M100 256 L256 290 L242 256 L256 222 Z" fill="#FBBF24" opacity="0.8" />

    <!-- Center Pivot Pin -->
    <circle cx="256" cy="256" r="28" fill="#143c30" stroke="url(#goldGrad)" stroke-width="6" />
    <circle cx="256" cy="256" r="10" fill="#F59E0B" />
  </g>
</svg>
`;

// Maskable icon SVG (with 15% safe padding around all critical content)
const maskableSvg = `
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1B4D3E" />
      <stop offset="50%" stop-color="#143c30" />
      <stop offset="100%" stop-color="#0e2a22" />
    </linearGradient>
    <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FCD34D" />
      <stop offset="100%" stop-color="#F59E0B" />
    </linearGradient>
    <linearGradient id="needleNorth" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#EF4444" />
      <stop offset="100%" stop-color="#B91C1C" />
    </linearGradient>
    <linearGradient id="needleSouth" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#F3F4F6" />
      <stop offset="100%" stop-color="#D1D5DB" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="130%" height="130%">
      <feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#000000" flood-opacity="0.35" />
    </filter>
  </defs>

  <!-- Full Bleed Background for Safe Area Masks -->
  <rect width="512" height="512" fill="url(#bgGrad)" />

  <!-- Center Group Scaled to 76% for Android Maskable Safe Area -->
  <g transform="translate(61.44, 61.44) scale(0.76)">
    <!-- Outer Ring -->
    <circle cx="256" cy="256" r="180" fill="none" stroke="url(#goldGrad)" stroke-width="6" opacity="0.45" />
    <circle cx="256" cy="256" r="160" fill="none" stroke="#FAF8F5" stroke-width="2" stroke-dasharray="8 12" opacity="0.3" />

    <circle cx="256" cy="86" r="7" fill="#F59E0B" />
    <circle cx="256" cy="426" r="7" fill="#F59E0B" opacity="0.6" />
    <circle cx="86" cy="256" r="7" fill="#F59E0B" opacity="0.6" />
    <circle cx="426" cy="256" r="7" fill="#F59E0B" opacity="0.6" />

    <g filter="url(#shadow)">
      <path d="M256 100 L290 256 L256 242 L222 256 Z" fill="url(#needleNorth)" />
      <path d="M256 412 L222 256 L256 270 L290 256 Z" fill="url(#needleSouth)" />
      <path d="M412 256 L256 222 L270 256 L256 290 Z" fill="#F59E0B" opacity="0.8" />
      <path d="M100 256 L256 290 L242 256 L256 222 Z" fill="#FBBF24" opacity="0.8" />

      <circle cx="256" cy="256" r="28" fill="#143c30" stroke="url(#goldGrad)" stroke-width="6" />
      <circle cx="256" cy="256" r="10" fill="#F59E0B" />
    </g>
  </g>
</svg>
`;

async function generate() {
  const standardBuffer = Buffer.from(standardSvg);
  const maskableBuffer = Buffer.from(maskableSvg);

  // 1. Generate favicon.svg
  fs.writeFileSync(path.join(publicDir, 'favicon.svg'), standardSvg);

  // 2. Generate pwa-512x512.png
  await sharp(standardBuffer)
    .resize(512, 512)
    .png()
    .toFile(path.join(publicDir, 'pwa-512x512.png'));
  console.log('✓ Created pwa-512x512.png');

  // 3. Generate pwa-192x192.png
  await sharp(standardBuffer)
    .resize(192, 192)
    .png()
    .toFile(path.join(publicDir, 'pwa-192x192.png'));
  console.log('✓ Created pwa-192x192.png');

  // 4. Generate maskable-icon-512x512.png
  await sharp(maskableBuffer)
    .resize(512, 512)
    .png()
    .toFile(path.join(publicDir, 'maskable-icon-512x512.png'));
  console.log('✓ Created maskable-icon-512x512.png');

  // 5. Generate apple-touch-icon.png (180x180)
  await sharp(standardBuffer)
    .resize(180, 180)
    .png()
    .toFile(path.join(publicDir, 'apple-touch-icon.png'));
  console.log('✓ Created apple-touch-icon.png');
}

generate().catch(console.error);

const fs = require('fs');
const path = require('path');

const dir = 'd:/Rodent Control Truckee';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.html'));

const footerSearch = '<a href="#" data-social="tiktok" aria-label="TikTok"';
const footerReplace = '<a href="https://www.tiktok.com/@rodentcontroltruckee?lang=en-GB" target="_blank" rel="noopener noreferrer" data-social="tiktok" aria-label="TikTok"';

const headerSearch = `        <a href="https://www.instagram.com/rodentcontroltruckee/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" style="color: inherit; text-decoration: none; display: inline-flex;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        </a>`;

const headerReplace = headerSearch + `\n        <a href="https://www.tiktok.com/@rodentcontroltruckee?lang=en-GB" target="_blank" rel="noopener noreferrer" aria-label="TikTok" style="color: inherit; text-decoration: none; display: inline-flex;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.7 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.74-.1z" /></svg>
        </a>`;

for (let file of files) {
    let filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    
    // Replace footer
    content = content.replace(footerSearch, footerReplace);
    
    // Replace header (only if TikTok isn't already there)
    if (!content.includes('aria-label="TikTok" style="color: inherit; text-decoration: none; display: inline-flex;"')) {
        content = content.replace(headerSearch, headerReplace);
    }
    
    fs.writeFileSync(filePath, content, 'utf8');
    console.log('Updated ' + file);
}

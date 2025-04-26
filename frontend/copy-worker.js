const fs = require('fs');
const path = require('path');

// Usa el worker incluido con react-pdf (pdfjs-dist 4.8.69)
const src = path.resolve(__dirname, 'node_modules/react-pdf/node_modules/pdfjs-dist/build/pdf.worker.min.mjs');
const dest = path.resolve(__dirname, 'public/pdf.worker.min.js');

fs.copyFileSync(src, dest);
console.log('Worker copiado desde pdfjs-dist@4.8.69');


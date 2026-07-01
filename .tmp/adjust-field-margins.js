/**
 * adjust-field-margins.js
 *
 * Edit the FIELD_RECTS values below to adjust the position and size of
 * every fillable field in the converted judging PDFs.
 *
 * PDF coordinates: origin (0,0) is BOTTOM-LEFT of the page.
 *   x1 = left edge of field
 *   y1 = bottom edge of field
 *   x2 = right edge of field
 *   y2 = top edge of field
 *
 * Page size: 595 x 842 pts (A4)
 * AWARDED column roughly spans x: 463 → 531
 *
 * Run with:
 *   node .tmp/adjust-field-margins.js
 */

const fs = require('fs');
const path = require('path');
const { PDFDocument, PDFName, PDFArray, PDFNumber } = require(
  path.resolve(__dirname, 'pdf-field-debug/node_modules/pdf-lib')
);

const PDF_DIR = path.resolve(__dirname, '../webroot/files/judgingformpdf');

// ─── ADJUST THESE VALUES ──────────────────────────────────────────────────────

const FIELD_RECTS = {
  // Individual score rows — AWARDED column
  '1A': [463.32, 546.12, 530.64, 561.60],
  '1B': [463.32, 528.96, 530.64, 544.32],
  '1C': [463.32, 511.80, 530.64, 527.16],
  '1D': [463.20, 495.48, 530.76, 510.12],
  '1E': [463.32, 478.32, 530.64, 493.80],
  '2A': [463.32, 444.00, 530.64, 459.36],
  '2B': [463.32, 426.84, 530.64, 442.20],
  '2C': [463.32, 409.56, 530.64, 425.04],
  '2D': [463.32, 392.40, 530.64, 407.88],
  '2E': [463.32, 375.24, 530.64, 390.72],
  '2F': [463.32, 358.47, 530.64, 373.95],
  '3A': [463.32, 323.76, 530.64, 339.24],
  '3B': [463.32, 307.08, 530.64, 321.96],

  // TOTAL POINTS — AWARDED column
  //   x1     y1     x2     y2
  'TOTAL': [464.20, 266.20, 529.80, 282.70],

  // Header / identity fields
  'Name':           [143.76, 652.80, 401.16, 670.44],
  'DOB':            [460.32, 652.80, 530.64, 670.44],
  'School':         [144.00, 627.84, 422.52, 650.88],
  'Cust Code':      [460.56, 627.84, 530.40, 650.88],

  // Comment and signature fields
  'COMMENT':        [ 66.36,  96.75, 530.64, 246.12],
  'Judges Name':    [ 94.89,  67.92, 300.81,  92.40],
  'JUDGE SIGNATURE':[347.94,  65.54, 531.37,  94.34],
};

// ─── FILES TO UPDATE ─────────────────────────────────────────────────────────

const FILES = [
  'E-ME.30 Video Production - new.pdf',
  'E-ME.28 Radio Play - new.pdf',
  'E-ME.26 Web Site - new.pdf',
  'E-ME.24 Powerpoint - new.pdf',
  'E-ME.20 Graphic Design - new.pdf',
  'E-ME.17 Posters - new.pdf',
  'E-ME.14 Digital Art - new.pdf',
  'E-ME.11 Composite Photography - new.pdf',
  'E-ME.10 Photography - new.pdf',
];

// ─── ENGINE (no need to edit below this line) ─────────────────────────────────

function makeRect(ctx, values) {
  const arr = PDFArray.withContext(ctx);
  for (const v of values) arr.push(PDFNumber.of(v));
  return arr;
}

function resolveFieldName(doc, widget) {
  const t = widget.get(PDFName.of('T'));
  if (t) return t.toString().replace(/[<>]/g, '').replace(/^FEFF/, '');

  const parentRef = widget.get(PDFName.of('Parent'));
  if (!parentRef) return null;

  const parent = doc.context.lookup(parentRef);
  const pt = parent?.get?.(PDFName.of('T'));
  if (!pt) return null;

  // Decode UTF-16BE hex string (e.g. <FEFF0054004F00540041004C> → "TOTAL")
  const raw = pt.toString();
  if (raw.startsWith('<')) {
    const hex = raw.slice(1, -1).replace(/^FEFF/, '');
    let name = '';
    for (let i = 0; i < hex.length; i += 4) {
      name += String.fromCharCode(parseInt(hex.slice(i, i + 4), 16));
    }
    return name;
  }
  return raw.replace(/[()]/g, '');
}

async function applyRects(file) {
  const fullPath = path.join(PDF_DIR, file);
  const doc = await PDFDocument.load(fs.readFileSync(fullPath), { ignoreEncryption: true });
  const page = doc.getPage(0);
  let changed = 0;

  for (const ref of page.node.lookup(PDFName.of('Annots')).asArray()) {
    const widget = doc.context.lookup(ref);
    const subtype = widget?.get?.(PDFName.of('Subtype'));
    if (!subtype || subtype.toString() !== '/Widget') continue;

    const name = resolveFieldName(doc, widget);
    if (!name || !FIELD_RECTS[name]) continue;

    widget.set(PDFName.of('Rect'), makeRect(doc.context, FIELD_RECTS[name]));
    widget.delete(PDFName.of('AP')); // clear stale appearance so viewer redraws
    changed++;
  }

  fs.writeFileSync(fullPath, await doc.save({ useObjectStreams: false, updateFieldAppearances: false }));
  console.log(`${file}: ${changed} field(s) updated`);
}

(async () => {
  for (const file of FILES) await applyRects(file);
  console.log('Done.');
})().catch(err => { console.error(err); process.exit(1); });

/**
 * TISHLA BY PURNIKA SALES - APPS SCRIPT BACKEND
 * Developed by Shakti Develops (@shaktidevelops)
 */

const MODEL_SHOOT_FOLDER_ID = "1imx6WnUPZ7xGPNASF0O06B9vDPi_5wok";
const TABLE_SHOOT_FOLDER_ID = "1uR2r5PiMLv6ZUKLrCPn1vgw-bAwBquqw";
const VISION_API_KEY = "YOUR_VISION_API_KEY_HERE"; 
const WHATSAPP_PHONE = "919574716712";

function onOpen() {
  SpreadsheetApp.getUi()
    .createMenu('👑 Tishla Sync')
    .addItem('🔄 Sync Drive Catalogues', 'syncDriveCataloguesToSheet')
    .addItem('⚡ Format Sheet Columns', 'formatSheetProperly')
    .addToUi();
}

function doGet(e) {
  // 1. Mobile 1-Tap Drive Sync Endpoint (?action=sync)
  if (e && e.parameter && e.parameter.action === 'sync') {
    const result = JSON.parse(syncDriveCataloguesToSheet());
    const serviceUrl = ScriptApp.getService().getUrl();
    return HtmlService.createHtmlOutput(`
      <!DOCTYPE html>
      <html>
        <head>
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Tishla Sync Status</title>
          <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
          <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; background: #FAF4E8; color: #36020E; text-align: center; padding: 40px 16px; margin: 0; }
            .card { background: #FFFDF9; max-width: 420px; margin: 20px auto; padding: 30px 20px; border-radius: 20px; border: 2px solid #D4AF37; box-shadow: 0 10px 30px rgba(54,2,14,0.15); }
            .btn { display: inline-block; margin-top: 20px; padding: 12px 26px; background: linear-gradient(135deg, #4D0618, #6F0A25); color: #FFFDF9; text-decoration: none; border-radius: 12px; font-weight: 700; }
          </style>
        </head>
        <body>
          <div class="card">
            <div style="font-size: 46px;">${result.success ? '✅' : '❌'}</div>
            <h2 style="margin: 12px 0 6px;">${result.success ? 'Sync Successful' : 'Sync Error'}</h2>
            <p style="font-size: 13.5px; color: #6E4D57; line-height: 1.5;">${result.message || result.error}</p>
            <a href="${serviceUrl}" class="btn">Return to Tishla Store</a>
          </div>
        </body>
      </html>
    `).setTitle('Sync Complete | Tishla by Purnika Sales');
  }

  // 2. REST API Endpoint for External Web Hosting / Hostinger (?action=getData)
  if (e && e.parameter && e.parameter.action === 'getData') {
    const data = getCatalogueData();
    return ContentService
      .createTextOutput(data)
      .setMimeType(ContentService.MimeType.JSON);
  }

  // 3. Default: Render Complete Web App (Index.html)
  return HtmlService.createTemplateFromFile('Index')
    .evaluate()
    .setTitle("Tishla by Purnika Sales | Official Saree Store")
    .addMetaTag('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=5.0')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

function include(filename) {
  try {
    return HtmlService.createHtmlOutputFromFile(filename).getContent();
  } catch (err) {
    if (filename === 'Branding') return '<div style="text-align:center; padding:10px; color:#D4AF37; font-size:12px;">© Shakti Develops</div>';
    return '';
  }
}

// Generate smart, meaningful SKU slug from name and shoot type
function generateSmartSku(name, shootType, index) {
  const clean = String(name || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 8);
  const shootCode = (shootType === 'Model Shoot') ? 'MS' : 'TS';
  return `TIS-${clean || 'CAT'}-${shootCode}`;
}

// Clean catalog name: remove leading "- ", leading "-", and all "*"
function cleanCatalogName(val) {
  if (!val) return '';
  let s = String(val).replace(/\*/g, '').trim();
  s = s.replace(/^[\s\-\–\—]+\s*/, '').trim();
  return toProperCase(s);
}

function toProperCase(value) {
  return String(value || '')
    .toLowerCase()
    .replace(/(^|[\s\-_\/.&()]+)([a-z\u00C0-\u024F])/g, function(match, prefix, letter) {
      return prefix + letter.toUpperCase();
    })
    .replace(/\s+/g, ' ')
    .trim();
}

function extractPriceOnly_(line) {
  const original = String(line || '').replace(/\*/g, '');
  const normalized = original.replace(/[\u00A0\u202F]/g, ' ').replace(/[：]/g, ':').trim();
  if (!/^Price\s*(?:Only)?\s*:/i.test(normalized)) return { value: '', reason: 'No Price line' };

  let tail = normalized.replace(/^Price\s*(?:Only)?\s*:\s*/i, '').trim();
  tail = tail.replace(/^(?:₹|Rs\.?|INR)\s*/i, '').replace(/\s*(?:\/\-|\-|Only)\s*$/i, '').trim();

  const numberMatch = tail.match(/\d[\d,\.\s]*(?:\.\d+)?/);
  if (!numberMatch) return { value: '', reason: 'No numeric price' };

  let token = numberMatch[0].replace(/\s+/g, '');
  if (/^\d{1,3}(?:[,.]\d{3})+$/.test(token)) token = token.replace(/[,.]/g, '');
  token = token.replace(/,/g, '');

  const numericPrice = Number(token);
  if (!isFinite(numericPrice) || numericPrice < 199 || numericPrice > 99999) {
    return { value: '', reason: 'Price out of range' };
  }
  return { value: numericPrice, reason: '' };
}

function extractTxtCatalogData(txt) {
  const cleanTxt = String(txt || '').replace(/\*/g, '').replace(/\r/g, '');
  const lines = cleanTxt.split('\n').map(l => l.trim()).filter(Boolean);
  
  let catalogName = '', price = '', fabric = 'Pure Dola Silk', type = 'Designer Saree';

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];

    const catalogMatch = line.match(/^Catalog\s*:\s*(.+)$/i);
    if (catalogMatch && !catalogName) {
      catalogName = cleanCatalogName(catalogMatch[1]);
    }

    const fabricMatch = line.match(/^(?:Fabric|Material)\s*:\s*(.+)$/i);
    if (fabricMatch) {
      fabric = toProperCase(fabricMatch[1].trim());
    }

    const typeMatch = line.match(/^(?:Type|Work|Collection)\s*:\s*(.+)$/i);
    if (typeMatch) {
      type = toProperCase(typeMatch[1].trim());
    }

    if (/^Price\s*(?:Only)?\s*:/i.test(line) && !price) {
      const extracted = extractPriceOnly_(line);
      if (extracted.value !== '') {
        price = extracted.value;
      }
    }
  }

  // Backup fallback: scan for ₹ or Rs
  if (!price) {
    for (let i = 0; i < lines.length; i++) {
      const line = lines[i];
      if (/₹|Rs\.?/i.test(line)) {
        const numMatch = line.match(/\d[\d,\.]+/);
        if (numMatch) {
          const num = Number(numMatch[0].replace(/,/g, ''));
          if (num >= 199 && num <= 99999) {
            price = num;
            break;
          }
        }
      }
    }
  }

  return { catalogName, price, fabric, type };
}

// ========================================================
// CATALOGUE DATABASE LOADER
// ========================================================
function getCatalogueData() {
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    if (!ss) return JSON.stringify({ success: false, error: "Script is not attached to Google Sheets. Open Extensions > Apps Script inside the sheet." });

    const sheet = ss.getSheetByName("Catalogue") || ss.getSheets()[0];
    const lastRow = sheet.getLastRow();
    const lastCol = sheet.getLastColumn();
    if (lastRow <= 1 || lastCol === 0) return JSON.stringify({ success: true, data: [] });

    const headers = sheet.getRange(1, 1, 1, lastCol).getValues()[0].map(h => String(h || '').trim().toLowerCase());
    const rawData = sheet.getRange(2, 1, lastRow - 1, lastCol).getValues();

    let idxSku = headers.findIndex(h => h.includes('sku') || h.includes('code'));
    let idxName = headers.findIndex(h => h.includes('catalogue') || h.includes('catalog') || h.includes('name'));
    let idxShoot = headers.findIndex(h => h.includes('shoot'));
    let idxFabric = headers.findIndex(h => h.includes('fabric') || h.includes('material'));
    let idxType = headers.findIndex(h => h.includes('type') && !h.includes('shoot'));
    let idxPrice = headers.findIndex(h => h.includes('price'));
    let idxColors = headers.findIndex(h => (h.includes('available') || h.includes('color')) && !h.includes('image') && !h.includes('out') && !h.includes('oos'));
    let idxFolder = headers.findIndex(h => h.includes('drive') || h.includes('folder'));
    let idxImages = headers.findIndex(h => h.includes('image'));

    if (idxSku === -1) idxSku = 0;
    if (idxName === -1) idxName = 1;
    if (idxShoot === -1) idxShoot = 2;
    if (idxPrice === -1) idxPrice = 3;
    if (idxFolder === -1) idxFolder = Math.min(5, lastCol - 1);
    if (idxImages === -1) idxImages = Math.min(6, lastCol - 1);

    const items = rawData.map((r, index) => {
      const priceStr = idxPrice !== -1 ? String(r[idxPrice] || '').replace(/[^0-9.]/g, '') : '';
      const rawImagesStr = (idxImages !== -1 && r[idxImages]) ? String(r[idxImages]).trim() : '';
      const rawImages = rawImagesStr.split(',').map(u => u.trim()).filter(u => u.length > 10);

      const nameVal = cleanCatalogName(r[idxName]);
      const shootVal = String(r[idxShoot] || 'Model Shoot').trim();

      let availableColors = [];
      if (idxColors !== -1 && r[idxColors]) {
        availableColors = String(r[idxColors]).split(',').map(s => s.trim()).filter(Boolean);
      } else {
        const variantCount = Math.max(0, rawImages.length - 1);
        for (let i = 1; i <= variantCount; i++) {
          availableColors.push(String(i * 100 + 1));
        }
      }

      const fabricVal = (idxFabric !== -1 && r[idxFabric]) ? String(r[idxFabric]).trim() : 'Pure Dola Silk';
      const typeVal = (idxType !== -1 && r[idxType]) ? String(r[idxType]).trim() : 'Designer Saree';
      
      let skuVal = String(r[idxSku] || '').trim();
      if (!skuVal || skuVal.startsWith('TPS-') || skuVal.startsWith('SS-')) {
        skuVal = generateSmartSku(nameVal, shootVal, index);
      }

      return {
        rowIndex: index + 2,
        sku: skuVal,
        name: nameVal,
        shootType: shootVal,
        fabric: fabricVal,
        categoryType: typeVal,
        price: parseFloat(priceStr) || 0,
        availableColors: availableColors,
        colorCount: availableColors.length,
        folderLink: idxFolder !== -1 ? String(r[idxFolder] || '').trim() : '',
        images: rawImages
      };
    }).filter(item => item.name.length > 0 || item.sku.length > 0);

    items.sort((a, b) => a.name.localeCompare(b.name));
    return JSON.stringify({ success: true, data: items });
  } catch (err) {
    return JSON.stringify({ success: false, error: err.toString() });
  }
}

function formatSheetProperly() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheet = ss.getSheetByName("Catalogue") || ss.getSheets()[0];
  sheet.setName("Catalogue");

  const headers = [
    "Design Code / SKU", "Catalogue Name", "Shoot Type", "Fabric / Material", 
    "Type", "Price (₹)", "Available Colors", "Drive Folder Link", "All Images (000 first)"
  ];

  const maxCols = sheet.getMaxColumns();
  if (maxCols > headers.length) sheet.deleteColumns(headers.length + 1, maxCols - headers.length);

  sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
  sheet.getRange(1, 1, 1, headers.length)
    .setBackground("#36020E")
    .setFontColor("#F4DA8A")
    .setFontWeight("bold")
    .setHorizontalAlignment("center");

  sheet.setFrozenRows(1);
  const maxRows = Math.max(sheet.getMaxRows(), 100);
  sheet.getRange(2, 6, maxRows - 1, 1).setNumberFormat("₹#,##0");
}

function syncDriveCataloguesToSheet() {
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const sheet = ss.getSheetByName("Catalogue") || ss.getSheets()[0];
    if (sheet.getLastRow() === 0) formatSheetProperly();

    const existingData = sheet.getDataRange().getValues();
    const existingRows = new Map();

    for (let i = 1; i < existingData.length; i++) {
      const name = cleanCatalogName(existingData[i][1]);
      const shootType = String(existingData[i][2] || '').trim();
      if (name || shootType) {
        existingRows.set((name + "___" + shootType).toLowerCase(), i + 1);
      }
    }

    const sources = [
      { folderId: MODEL_SHOOT_FOLDER_ID, shootType: "Model Shoot" },
      { folderId: TABLE_SHOOT_FOLDER_ID, shootType: "Table Shoot" }
    ];

    let rowsToInsert = [];
    let updatedCount = 0;
    let count = Math.max(sheet.getLastRow() - 1, 0);

    sources.forEach(source => {
      try {
        const rootFolder = DriveApp.getFolderById(source.folderId);
        const subFolders = rootFolder.getFolders();

        while (subFolders.hasNext()) {
          const folder = subFolders.next();
          const folderName = folder.getName().trim();
          const files = folder.getFiles();

          let mainImage000 = null;
          const colorVariants = {};
          const colorCodesSet = new Set();
          let txtContent = "";

          while (files.hasNext()) {
            const file = files.next();
            const mime = file.getMimeType();
            const name = file.getName().trim();

            if (mime === "text/plain" || name.toLowerCase().endsWith(".txt")) {
              if (!txtContent) txtContent = file.getBlob().getDataAsString("UTF-8").trim();
              continue;
            }

            if (mime.startsWith("image/") || name.match(/\.(jpg|jpeg|png|webp)$/i)) {
              const fileUrl = "https://lh3.googleusercontent.com/d/" + file.getId();
              
              if (/^000\b/i.test(name) || name.startsWith("000")) {
                mainImage000 = { name, url: fileUrl };
                continue;
              }

              const numMatch = name.match(/^(\d+)/);
              if (numMatch) {
                const num = parseInt(numMatch[1], 10);
                if (num === 0) {
                  mainImage000 = { name, url: fileUrl };
                } else {
                  const baseGroup = Math.floor(num / 100);
                  const baseCode = (baseGroup * 100) + 1;
                  colorCodesSet.add(baseCode.toString());

                  if (!colorVariants[baseGroup]) colorVariants[baseGroup] = [];
                  colorVariants[baseGroup].push({ name, url: fileUrl });
                }
              } else {
                if (!colorVariants['misc']) colorVariants['misc'] = [];
                colorVariants['misc'].push({ name, url: fileUrl });
              }
            }
          }

          const finalImages = [];
          if (mainImage000) finalImages.push(mainImage000.url);

          const sortedGroups = Object.keys(colorVariants).filter(k => k !== 'misc').sort((a,b) => Number(a)-Number(b));
          sortedGroups.forEach(grp => {
            colorVariants[grp].sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true }));
            finalImages.push(colorVariants[grp][0].url);
          });

          if (colorVariants['misc']) {
            colorVariants['misc'].forEach(img => finalImages.push(img.url));
          }

          const txtData = extractTxtCatalogData(txtContent);
          const finalCatalogName = cleanCatalogName(txtData.catalogName || folderName);
          const finalPrice = txtData.price === '' ? '' : txtData.price;
          const finalFabric = txtData.fabric || 'Pure Dola Silk';
          const finalType = txtData.type || (source.shootType === 'Model Shoot' ? 'Designer Saree' : 'Traditional Weave');
          const availableColorsString = Array.from(colorCodesSet).sort((a,b) => Number(a)-Number(b)).join(', ');

          const rowKey = (finalCatalogName + "___" + source.shootType).toLowerCase();
          const existingRow = existingRows.get(rowKey);

          if (existingRow) {
            sheet.getRange(existingRow, 2, 1, 1).setValue(finalCatalogName);
            sheet.getRange(existingRow, 3, 1, 1).setValue(source.shootType);
            sheet.getRange(existingRow, 4, 1, 1).setValue(finalFabric);
            sheet.getRange(existingRow, 5, 1, 1).setValue(finalType);
            if (finalPrice !== '') sheet.getRange(existingRow, 6, 1, 1).setValue(finalPrice);
            sheet.getRange(existingRow, 7, 1, 1).setValue(availableColorsString);
            sheet.getRange(existingRow, 8, 1, 2).setValues([[folder.getUrl(), finalImages.join(", ")]]);
            updatedCount++;
          } else {
            count++;
            const smartSku = generateSmartSku(finalCatalogName, source.shootType, count);
            rowsToInsert.push([
              smartSku,
              finalCatalogName,
              source.shootType,
              finalFabric,
              finalType,
              finalPrice,
              availableColorsString,
              folder.getUrl(),
              finalImages.join(", ")
            ]);
            existingRows.set(rowKey, 0);
          }
        }
      } catch (e) {
        console.error("Folder sync error:", e);
      }
    });

    if (rowsToInsert.length > 0) {
      sheet.getRange(sheet.getLastRow() + 1, 1, rowsToInsert.length, 9).setValues(rowsToInsert);
    }

    return JSON.stringify({
      success: true,
      message: `Sync complete. Added ${rowsToInsert.length} new catalogues and refreshed ${updatedCount} existing catalogues.`
    });
  } catch (e) {
    return JSON.stringify({ success: false, error: e.toString() });
  }
}

function analyzeImageWithVisionAPI(base64String) {
  try {
    if (VISION_API_KEY === "YOUR_VISION_API_KEY_HERE") {
      return JSON.stringify({ success: false, error: "Vision API Key is not configured." });
    }

    const base64Data = base64String.split(',')[1];
    const url = "https://vision.googleapis.com/v1/images:annotate?key=" + VISION_API_KEY;
    
    const payload = {
      requests: [{
        image: { content: base64Data },
        features: [
          { type: "LABEL_DETECTION", maxResults: 5 }, 
          { type: "TEXT_DETECTION", maxResults: 2 },  
          { type: "IMAGE_PROPERTIES", maxResults: 1 } 
        ]
      }]
    };

    const options = {
      method: "post",
      contentType: "application/json",
      payload: JSON.stringify(payload),
      muteHttpExceptions: true
    };

    const response = UrlFetchApp.fetch(url, options);
    const result = JSON.parse(response.getContentText());
    if (result.error) return JSON.stringify({ success: false, error: result.error.message });

    let searchKeywords = [];
    const annotations = result.responses[0];
    if (annotations.labelAnnotations) {
      annotations.labelAnnotations.forEach(label => {
        const desc = label.description.toLowerCase();
        if (desc !== "clothing" && desc !== "sari" && desc !== "textile") searchKeywords.push(desc);
      });
    }
    return JSON.stringify({ success: true, keywords: searchKeywords.join(" ") });
  } catch (err) {
    return JSON.stringify({ success: false, error: err.toString() });
  }
}
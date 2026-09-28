BEGIN;

INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-NOOR-001','noor-dola-silk-saree','Noor Dola Silk Saree','Statement Dola silk drape with a rich wine finish.','A signature Tishla saree designed for elevated festive dressing.',d.id,'Dola Silk','Saree','Model Shoot',2499,5,'active',true,1,'Bestseller'
FROM store.departments d WHERE d.slug='sarees' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-NOOR-001');
INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-HER-002','zari-heritage-silk','Zari Heritage Silk Saree','Classic silk with heritage zari detailing.','An occasion-ready silk saree with traditional richness and a clean modern finish.',d.id,'Pure Silk','Saree','Model Shoot',3899,5,'active',true,1,'New'
FROM store.departments d WHERE d.slug='sarees' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-HER-002');
INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-PET-003','petal-organza-party-set','Petal Organza Party Saree','Light-catching organza with a soft rose palette.','A luminous party edit piece built for evening celebrations.',d.id,'Organza','Saree','Model Shoot',2799,5,'active',true,1,'Trending'
FROM store.departments d WHERE d.slug='sarees' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-PET-003');
INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-AUR-004','aurora-georgette-drape','Aurora Georgette Drape','Fluid georgette in a jewel-toned emerald.','A graceful contemporary drape for festive and evening styling.',d.id,'Georgette','Saree','Table Shoot',2199,5,'active',false,1,'New'
FROM store.departments d WHERE d.slug='sarees' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-AUR-004');
INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-RBL-005','royal-brocade-lehenga','Royal Brocade Lehenga Set','A celebration-ready brocade set with luminous gold texture.','A statement lehenga set designed for wedding celebrations.',d.id,'Brocade','Lehenga','Model Shoot',6499,5,'active',true,1,'Exclusive'
FROM store.departments d WHERE d.slug='lehengas' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-RBL-005');
INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-MAR-006','marigold-embroidered-kurti-set','Marigold Embroidered Kurti Set','Polished everyday-to-festive coordinates.','A versatile set with crafted embroidery and an easy silhouette.',d.id,'Viscose','Kurti Set','Table Shoot',1899,5,'active',true,1,'Best Seller'
FROM store.departments d WHERE d.slug='kurtis-sets' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-MAR-006');
INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-GWN-007','ivory-evening-gown','Ivory Evening Gown','An elegant evening silhouette in soft ivory crepe.','A fluid evening gown for receptions, parties and formal occasions.',d.id,'Crepe','Gown','Model Shoot',4999,5,'active',false,1,'Limited'
FROM store.departments d WHERE d.slug='gowns' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-GWN-007');
INSERT INTO store.products(sku,slug,name,short_description,description,department_id,fabric,product_type,shoot_type,base_price,gst_rate,status,featured,min_order_qty,product_badge)
SELECT 'TS-SUT-008','midnight-sequin-suit','Midnight Sequin Suit','A deep midnight party suit with fine shimmer.','An evening-ready suit for statement party dressing.',d.id,'Georgette','Suit','Model Shoot',3199,5,'active',false,1,'Party Edit'
FROM store.departments d WHERE d.slug='suits' AND NOT EXISTS(SELECT 1 FROM store.products WHERE sku='TS-SUT-008');

INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-WINE','Wine','Wine','#5A1025',p.base_price,p.base_price+500,true FROM store.products p WHERE p.sku='TS-NOOR-001' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-WINE');
INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-CRIMSON','Crimson','Crimson','#8B2B3E',p.base_price,p.base_price+600,true FROM store.products p WHERE p.sku='TS-HER-002' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-CRIMSON');
INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-ROSE','Rose','Rose','#B96B7D',p.base_price,p.base_price+500,true FROM store.products p WHERE p.sku='TS-PET-003' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-ROSE');
INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-EMERALD','Emerald','Emerald','#0B6B54',p.base_price,p.base_price+500,true FROM store.products p WHERE p.sku='TS-AUR-004' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-EMERALD');
INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-GOLD','Gold','Gold','#C9A227',p.base_price,p.base_price+1000,true FROM store.products p WHERE p.sku='TS-RBL-005' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-GOLD');
INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-MARIGOLD','Marigold','Marigold','#C7942B',p.base_price,p.base_price+400,true FROM store.products p WHERE p.sku='TS-MAR-006' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-MARIGOLD');
INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-IVORY','Ivory','Ivory','#E8DDC8',p.base_price,p.base_price+1000,true FROM store.products p WHERE p.sku='TS-GWN-007' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-IVORY');
INSERT INTO store.product_variants(product_id,sku,name,color_name,color_hex,price,compare_at_price,is_active)
SELECT p.id,p.sku||'-MIDNIGHT','Midnight','Midnight','#202538',p.base_price,p.base_price+600,true FROM store.products p WHERE p.sku='TS-SUT-008' AND NOT EXISTS(SELECT 1 FROM store.product_variants WHERE sku=p.sku||'-MIDNIGHT');

INSERT INTO store.variant_prices(price_list_id,variant_id,price,compare_at_price)
SELECT pl.id,v.id,v.price,v.compare_at_price FROM store.price_lists pl JOIN store.product_variants v ON pl.code='RETAIL'
WHERE NOT EXISTS(SELECT 1 FROM store.variant_prices vp WHERE vp.price_list_id=pl.id AND vp.variant_id=v.id);

INSERT INTO store.inventory_stock(location_id,variant_id,on_hand,reserved,reorder_level)
SELECT l.id,v.id,25,0,5 FROM store.inventory_locations l CROSS JOIN store.product_variants v
WHERE l.code='SURAT_MAIN' AND NOT EXISTS(SELECT 1 FROM store.inventory_stock s WHERE s.location_id=l.id AND s.variant_id=v.id);

INSERT INTO store.collection_products(collection_id,product_id,sort_order)
SELECT c.id,p.id,10 FROM store.collections c CROSS JOIN store.products p WHERE c.slug='new-arrivals' AND p.status='active' AND NOT EXISTS(SELECT 1 FROM store.collection_products cp WHERE cp.collection_id=c.id AND cp.product_id=p.id);
INSERT INTO store.collection_products(collection_id,product_id,sort_order)
SELECT c.id,p.id,10 FROM store.collections c CROSS JOIN store.products p WHERE c.slug='bestsellers' AND p.featured AND NOT EXISTS(SELECT 1 FROM store.collection_products cp WHERE cp.collection_id=c.id AND cp.product_id=p.id);
INSERT INTO store.collection_products(collection_id,product_id,sort_order)
SELECT c.id,p.id,10 FROM store.collections c CROSS JOIN store.products p WHERE c.slug='wedding-edit' AND p.sku IN ('TS-RBL-005','TS-HER-002','TS-NOOR-001') AND NOT EXISTS(SELECT 1 FROM store.collection_products cp WHERE cp.collection_id=c.id AND cp.product_id=p.id);



-- Demo media so the database-backed storefront has a complete visual catalogue.
INSERT INTO store.media_assets(storage_provider,public_url,alt_text)
SELECT 'external_url',x.url,x.alt_text
FROM (VALUES
('https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=1200&q=88','Noor Dola Silk Saree'),
('https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1200&q=88','Zari Heritage Silk Saree'),
('https://images.unsplash.com/photo-1583391733956-6c78276477e2?auto=format&fit=crop&w=1200&q=88','Petal Organza Party Saree'),
('https://images.unsplash.com/photo-1621784563330-caee0b138a00?auto=format&fit=crop&w=1200&q=88','Aurora Georgette Drape'),
('https://images.unsplash.com/photo-1610189012906-5e132fbbc77f?auto=format&fit=crop&w=1200&q=88','Royal Brocade Lehenga Set'),
('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=1200&q=88','Marigold Embroidered Kurti Set'),
('https://images.unsplash.com/photo-1539008835657-9e8e9680c956?auto=format&fit=crop&w=1200&q=88','Ivory Evening Gown'),
('https://images.unsplash.com/photo-1591369822096-ffd140ec948f?auto=format&fit=crop&w=1200&q=88','Midnight Sequin Suit')
) AS x(url,alt_text)
WHERE NOT EXISTS(SELECT 1 FROM store.media_assets ma WHERE ma.public_url=x.url);

INSERT INTO store.product_images(product_id,media_asset_id,sort_order,is_primary)
SELECT p.id,ma.id,0,true FROM store.products p JOIN store.media_assets ma ON ma.alt_text=p.name
WHERE NOT EXISTS(SELECT 1 FROM store.product_images pi WHERE pi.product_id=p.id AND pi.media_asset_id=ma.id);

INSERT INTO store.reviews(product_id,rating,title,body,status,verified_purchase)
SELECT p.id,r.rating,r.title,r.body,'approved',true FROM store.products p
JOIN (VALUES
('TS-NOOR-001',5,'Beautiful drape','The wine finish and silk texture are lovely.'),
('TS-HER-002',5,'Heritage feel','Elegant for wedding events and photographs beautifully.'),
('TS-MAR-006',4,'Easy festive set','Comfortable and easy to style.'),
('TS-RBL-005',5,'Statement piece','Rich detail and celebration-ready.'),
('TS-GWN-007',5,'Elegant silhouette','Clean, fluid and polished.')
) AS r(sku,rating,title,body) ON r.sku=p.sku
WHERE NOT EXISTS(SELECT 1 FROM store.reviews rv WHERE rv.product_id=p.id AND rv.title=r.title);

COMMIT;

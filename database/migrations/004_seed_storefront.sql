BEGIN;

INSERT INTO store.departments(name,slug,description,hero_image_url,sort_order)
VALUES
('Sarees','sarees','Silk, organza, georgette and statement drapes.','/branding/banner.png',10),
('Lehengas','lehengas','Celebration-ready sets for wedding season.','/branding/banner.png',20),
('Kurtis & Sets','kurtis-sets','Polished everyday and festive coordinates.','/branding/banner.png',30),
('Suits','suits','Tailored silhouettes with occasion-ready detail.','/branding/banner.png',40),
('Gowns','gowns','Fluid evening shapes and statement dressing.','/branding/banner.png',50),
('Dupattas','dupattas','Layering pieces with luxe texture.','/branding/banner.png',60),
('Blouses','blouses','Designed to complete the drape.','/branding/banner.png',70),
('Accessories','accessories','Finishing touches for the full look.','/branding/banner.png',80)
ON CONFLICT(slug) DO UPDATE SET description=EXCLUDED.description, sort_order=EXCLUDED.sort_order;

INSERT INTO store.collections(name,slug,description,hero_image_url,banner_image_url,collection_type,sort_order,is_featured)
VALUES
('New Arrivals','new-arrivals','Fresh silhouettes, colours and textures for the new season.','/branding/banner.png','/branding/banner.png','seasonal',10,true),
('Bestsellers','bestsellers','Signature Tishla styles customers keep coming back for.','/branding/banner.png','/branding/banner.png','smart',20,true),
('Wedding Edit','wedding-edit','Silk, zari and statement dressing for every wedding event.','/branding/banner.png','/branding/banner.png','campaign',30,true),
('Festive Edit','festive-edit','Rich colour, shimmer and craftsmanship for the season.','/branding/banner.png','/branding/banner.png','campaign',40,false),
('Party Edit','party-edit','Sequins, shimmer and evening-ready silhouettes.','/branding/banner.png','/branding/banner.png','campaign',50,false),
('Ready to Ship','ready-to-ship','Curated pieces available for quick dispatch.','/branding/banner.png','/branding/banner.png','smart',60,false),
('Sale','sale','Special pricing on selected Tishla pieces.','/branding/banner.png','/branding/banner.png','smart',70,false)
ON CONFLICT(slug) DO UPDATE SET description=EXCLUDED.description,sort_order=EXCLUDED.sort_order,is_featured=EXCLUDED.is_featured;

INSERT INTO cms.navigation_menus(code,name,location)
VALUES ('HEADER','Main Header','header'),('FOOTER_SHOP','Footer Shop','footer'),('FOOTER_DISCOVER','Footer Discover','footer'),('FOOTER_CARE','Footer Customer Care','footer')
ON CONFLICT(code) DO NOTHING;

INSERT INTO cms.navigation_items(menu_id,label,url,item_type,sort_order)
SELECT id,'NEW IN','/collections/new-arrivals','collection',10 FROM cms.navigation_menus WHERE code='HEADER' AND NOT EXISTS(SELECT 1 FROM cms.navigation_items i JOIN cms.navigation_menus m ON m.id=i.menu_id WHERE m.code='HEADER' AND i.label='NEW IN');
INSERT INTO cms.navigation_items(menu_id,label,url,item_type,sort_order)
SELECT id,'Shop','/shop','link',20 FROM cms.navigation_menus WHERE code='HEADER' AND NOT EXISTS(SELECT 1 FROM cms.navigation_items i JOIN cms.navigation_menus m ON m.id=i.menu_id WHERE m.code='HEADER' AND i.label='Shop');
INSERT INTO cms.navigation_items(menu_id,label,url,item_type,sort_order)
SELECT id,'Collections','/collections/new-arrivals','link',30 FROM cms.navigation_menus WHERE code='HEADER' AND NOT EXISTS(SELECT 1 FROM cms.navigation_items i JOIN cms.navigation_menus m ON m.id=i.menu_id WHERE m.code='HEADER' AND i.label='Collections');
INSERT INTO cms.navigation_items(menu_id,label,url,item_type,sort_order)
SELECT id,'Occasions','/collections/wedding-edit','link',40 FROM cms.navigation_menus WHERE code='HEADER' AND NOT EXISTS(SELECT 1 FROM cms.navigation_items i JOIN cms.navigation_menus m ON m.id=i.menu_id WHERE m.code='HEADER' AND i.label='Occasions');
INSERT INTO cms.navigation_items(menu_id,label,url,item_type,sort_order)
SELECT id,'LOOKBOOK','/lookbook','link',50 FROM cms.navigation_menus WHERE code='HEADER' AND NOT EXISTS(SELECT 1 FROM cms.navigation_items i JOIN cms.navigation_menus m ON m.id=i.menu_id WHERE m.code='HEADER' AND i.label='LOOKBOOK');
INSERT INTO cms.navigation_items(menu_id,label,url,item_type,sort_order)
SELECT id,'WHOLESALE','/wholesale','link',60 FROM cms.navigation_menus WHERE code='HEADER' AND NOT EXISTS(SELECT 1 FROM cms.navigation_items i JOIN cms.navigation_menus m ON m.id=i.menu_id WHERE m.code='HEADER' AND i.label='WHOLESALE');
INSERT INTO cms.navigation_items(menu_id,label,url,item_type,sort_order)
SELECT id,'SALE','/collections/sale','link',70 FROM cms.navigation_menus WHERE code='HEADER' AND NOT EXISTS(SELECT 1 FROM cms.navigation_items i JOIN cms.navigation_menus m ON m.id=i.menu_id WHERE m.code='HEADER' AND i.label='SALE');

INSERT INTO cms.home_sections(section_key,eyebrow,title,content_json,sort_order)
VALUES
('hero','THE TISHLA EDIT','Indian craft. Contemporary spirit.','{"cta":[{"label":"Shop New Arrivals","url":"/collections/new-arrivals"},{"label":"Explore Sarees","url":"/departments/sarees"}]}',10),
('departments','SHOP THE DEPARTMENTS','Designed for every kind of occasion','{}',20),
('new-arrivals','NEW ARRIVALS','A fresh take on Indian dressing','{}',30),
('wedding','THE WEDDING EDIT','For vows, festivities & everything between.','{}',40),
('occasions','SHOP BY OCCASION','Find the look by the moment','{}',50),
('craft','SURAT • INDIA','Made close to the craft.','{}',60),
('newsletter','JOIN THE EDIT','First access to new drops.','{}',70)
ON CONFLICT(section_key) DO NOTHING;

INSERT INTO cms.pages(slug,title,excerpt,template_key,status)
VALUES
('about','About Tishla','Indian craft with a modern point of view.','story','published'),
('contact','Contact Tishla','We are here for you.','contact','published'),
('shipping','Shipping & Delivery', 'Delivery information for Tishla orders.','policy','published'),
('returns','Returns', 'Return guidance for eligible products.','policy','published'),
('faq','Frequently Asked Questions','Answers to common questions.','faq','published'),
('size-guide','Size Guide','Fit and sizing guidance.','policy','published')
ON CONFLICT(slug) DO NOTHING;

INSERT INTO cms.faq_entries(category,question,answer_html,sort_order)
VALUES
('Orders','How do I place an order?','Add products to your bag and continue through checkout, or contact Tishla on WhatsApp for product assistance.',10),
('Shipping','Where does Tishla ship?','We support shipping across India. Dispatch timing is shown during checkout.',20),
('Wholesale','How do I become a wholesale buyer?','Apply through the Wholesale section. Approved accounts can receive account-level pricing and bulk ordering tools.',30)
ON CONFLICT DO NOTHING;

COMMIT;

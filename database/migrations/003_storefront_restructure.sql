BEGIN;

CREATE SCHEMA IF NOT EXISTS cms;

CREATE TABLE IF NOT EXISTS store.departments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    parent_id uuid REFERENCES store.departments(id) ON DELETE SET NULL,
    name text NOT NULL,
    slug text NOT NULL UNIQUE,
    description text,
    hero_image_url text,
    icon_key text,
    sort_order integer NOT NULL DEFAULT 0,
    is_active boolean NOT NULL DEFAULT true,
    seo_title text,
    seo_description text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.collections (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name text NOT NULL,
    slug text NOT NULL UNIQUE,
    description text,
    hero_image_url text,
    banner_image_url text,
    collection_type text NOT NULL DEFAULT 'manual' CHECK (collection_type IN ('manual','smart','seasonal','campaign')),
    rule_json jsonb NOT NULL DEFAULT '{}'::jsonb,
    sort_order integer NOT NULL DEFAULT 0,
    is_featured boolean NOT NULL DEFAULT false,
    is_active boolean NOT NULL DEFAULT true,
    starts_at timestamptz,
    ends_at timestamptz,
    seo_title text,
    seo_description text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

ALTER TABLE store.products ADD COLUMN IF NOT EXISTS department_id uuid REFERENCES store.departments(id) ON DELETE SET NULL;
ALTER TABLE store.products ADD COLUMN IF NOT EXISTS merchandising_rank integer NOT NULL DEFAULT 0;
ALTER TABLE store.products ADD COLUMN IF NOT EXISTS product_badge text;
ALTER TABLE store.products ADD COLUMN IF NOT EXISTS care_instructions text;
ALTER TABLE store.products ADD COLUMN IF NOT EXISTS shipping_notes text;
ALTER TABLE store.products ADD COLUMN IF NOT EXISTS fit_notes text;

CREATE TABLE IF NOT EXISTS store.collection_products (
    collection_id uuid NOT NULL REFERENCES store.collections(id) ON DELETE CASCADE,
    product_id uuid NOT NULL REFERENCES store.products(id) ON DELETE CASCADE,
    sort_order integer NOT NULL DEFAULT 0,
    created_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (collection_id, product_id)
);

CREATE TABLE IF NOT EXISTS store.wishlists (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id uuid NOT NULL REFERENCES store.customers(id) ON DELETE CASCADE,
    name text NOT NULL DEFAULT 'My Wishlist',
    is_default boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE UNIQUE INDEX IF NOT EXISTS ux_wishlist_default ON store.wishlists(customer_id) WHERE is_default;

CREATE TABLE IF NOT EXISTS store.wishlist_items (
    wishlist_id uuid NOT NULL REFERENCES store.wishlists(id) ON DELETE CASCADE,
    product_id uuid NOT NULL REFERENCES store.products(id) ON DELETE CASCADE,
    created_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (wishlist_id, product_id)
);

CREATE TABLE IF NOT EXISTS store.recently_viewed (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id uuid REFERENCES store.customers(id) ON DELETE CASCADE,
    session_key text,
    product_id uuid NOT NULL REFERENCES store.products(id) ON DELETE CASCADE,
    last_viewed_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.reviews (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id uuid NOT NULL REFERENCES store.products(id) ON DELETE CASCADE,
    customer_id uuid REFERENCES store.customers(id) ON DELETE SET NULL,
    order_id uuid REFERENCES store.orders(id) ON DELETE SET NULL,
    rating smallint NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title text,
    body text,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
    verified_purchase boolean NOT NULL DEFAULT false,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.pages (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    slug text NOT NULL UNIQUE,
    title text NOT NULL,
    excerpt text,
    body_html text,
    template_key text NOT NULL DEFAULT 'standard',
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','archived')),
    published_at timestamptz,
    seo_title text,
    seo_description text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.banners (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    placement text NOT NULL,
    title text,
    subtitle text,
    cta_label text,
    cta_url text,
    image_url text,
    mobile_image_url text,
    sort_order integer NOT NULL DEFAULT 0,
    is_active boolean NOT NULL DEFAULT true,
    starts_at timestamptz,
    ends_at timestamptz,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.home_sections (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    section_key text NOT NULL UNIQUE,
    title text,
    eyebrow text,
    content_json jsonb NOT NULL DEFAULT '{}'::jsonb,
    sort_order integer NOT NULL DEFAULT 0,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.navigation_menus (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    code text NOT NULL UNIQUE,
    name text NOT NULL,
    location text NOT NULL DEFAULT 'header',
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.navigation_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    menu_id uuid NOT NULL REFERENCES cms.navigation_menus(id) ON DELETE CASCADE,
    parent_id uuid REFERENCES cms.navigation_items(id) ON DELETE CASCADE,
    label text NOT NULL,
    url text,
    item_type text NOT NULL DEFAULT 'link' CHECK (item_type IN ('link','department','collection','page','promo')),
    icon_key text,
    promo_image_url text,
    sort_order integer NOT NULL DEFAULT 0,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.lookbooks (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    slug text NOT NULL UNIQUE,
    title text NOT NULL,
    description text,
    hero_image_url text,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','archived')),
    published_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.lookbook_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    lookbook_id uuid NOT NULL REFERENCES cms.lookbooks(id) ON DELETE CASCADE,
    media_asset_id uuid REFERENCES store.media_assets(id) ON DELETE SET NULL,
    title text,
    body text,
    link_url text,
    sort_order integer NOT NULL DEFAULT 0,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb
);

CREATE TABLE IF NOT EXISTS cms.blog_posts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    slug text NOT NULL UNIQUE,
    title text NOT NULL,
    excerpt text,
    body_html text,
    cover_image_url text,
    author_name text,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published','archived')),
    published_at timestamptz,
    seo_title text,
    seo_description text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS cms.faq_entries (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    category text NOT NULL DEFAULT 'General',
    question text NOT NULL,
    answer_html text NOT NULL,
    sort_order integer NOT NULL DEFAULT 0,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE OR REPLACE VIEW store.v_storefront_products AS
SELECT
    p.id,
    p.sku,
    p.slug,
    p.name,
    p.short_description,
    p.description,
    p.fabric,
    p.product_type,
    p.shoot_type,
    p.base_price,
    p.currency,
    p.gst_rate,
    p.featured,
    p.product_badge,
    p.merchandising_rank,
    p.department_id,
    d.name AS department_name,
    d.slug AS department_slug,
    COALESCE(MIN(vp.price), MIN(v.price), p.base_price) AS price,
    MAX(COALESCE(v.compare_at_price, vp.compare_at_price)) AS compare_at_price,
    COALESCE(jsonb_agg(DISTINCT jsonb_build_object('id',v.id,'sku',v.sku,'name',v.name,'color',v.color_name,'color_hex',v.color_hex,'price',COALESCE(vp.price,v.price,p.base_price))) FILTER (WHERE v.id IS NOT NULL),'[]'::jsonb) AS variants,
    COALESCE(jsonb_agg(DISTINCT jsonb_build_object('url',ma.public_url,'sort_order',pi.sort_order,'is_primary',pi.is_primary,'alt_text',ma.alt_text)) FILTER (WHERE pi.id IS NOT NULL),'[]'::jsonb) AS images,
    COALESCE((SELECT round(avg(r.rating),2) FROM store.reviews r WHERE r.product_id=p.id AND r.status='approved'),0) AS rating,
    (SELECT count(*) FROM store.reviews r WHERE r.product_id=p.id AND r.status='approved') AS review_count,
    p.created_at,
    p.updated_at,
    p.search_document
FROM store.products p
LEFT JOIN store.departments d ON d.id=p.department_id
LEFT JOIN store.product_variants v ON v.product_id=p.id AND v.is_active
LEFT JOIN store.price_lists pl ON pl.code='RETAIL' AND pl.is_active
LEFT JOIN store.variant_prices vp ON vp.variant_id=v.id AND vp.price_list_id=pl.id
LEFT JOIN store.product_images pi ON pi.product_id=p.id
LEFT JOIN store.media_assets ma ON ma.id=pi.media_asset_id
GROUP BY p.id,d.name,d.slug;

CREATE OR REPLACE VIEW cms.v_navigation_items AS
SELECT m.code AS menu_code,m.location,i.id,i.parent_id,i.label,i.url,i.item_type,i.icon_key,i.promo_image_url,i.sort_order
FROM cms.navigation_menus m JOIN cms.navigation_items i ON i.menu_id=m.id
WHERE m.is_active AND i.is_active;

CREATE INDEX IF NOT EXISTS idx_departments_active_order ON store.departments(is_active,sort_order);
CREATE INDEX IF NOT EXISTS idx_collections_active_order ON store.collections(is_active,sort_order);
CREATE INDEX IF NOT EXISTS idx_collection_products_product ON store.collection_products(product_id);
CREATE INDEX IF NOT EXISTS idx_reviews_product_status ON store.reviews(product_id,status);
CREATE INDEX IF NOT EXISTS idx_recently_viewed_customer ON store.recently_viewed(customer_id,last_viewed_at DESC);
CREATE INDEX IF NOT EXISTS idx_navigation_parent_order ON cms.navigation_items(parent_id,sort_order);

-- updated_at triggers for new mutable tables
DROP TRIGGER IF EXISTS trg_departments_updated ON store.departments;
CREATE TRIGGER trg_departments_updated BEFORE UPDATE ON store.departments FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_collections_updated ON store.collections;
CREATE TRIGGER trg_collections_updated BEFORE UPDATE ON store.collections FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_wishlists_updated ON store.wishlists;
CREATE TRIGGER trg_wishlists_updated BEFORE UPDATE ON store.wishlists FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_reviews_updated ON store.reviews;
CREATE TRIGGER trg_reviews_updated BEFORE UPDATE ON store.reviews FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_pages_updated ON cms.pages;
CREATE TRIGGER trg_pages_updated BEFORE UPDATE ON cms.pages FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_banners_updated ON cms.banners;
CREATE TRIGGER trg_banners_updated BEFORE UPDATE ON cms.banners FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_home_sections_updated ON cms.home_sections;
CREATE TRIGGER trg_home_sections_updated BEFORE UPDATE ON cms.home_sections FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_navigation_menus_updated ON cms.navigation_menus;
CREATE TRIGGER trg_navigation_menus_updated BEFORE UPDATE ON cms.navigation_menus FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_navigation_items_updated ON cms.navigation_items;
CREATE TRIGGER trg_navigation_items_updated BEFORE UPDATE ON cms.navigation_items FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_lookbooks_updated ON cms.lookbooks;
CREATE TRIGGER trg_lookbooks_updated BEFORE UPDATE ON cms.lookbooks FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_blog_posts_updated ON cms.blog_posts;
CREATE TRIGGER trg_blog_posts_updated BEFORE UPDATE ON cms.blog_posts FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
DROP TRIGGER IF EXISTS trg_faq_entries_updated ON cms.faq_entries;
CREATE TRIGGER trg_faq_entries_updated BEFORE UPDATE ON cms.faq_entries FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

COMMIT;

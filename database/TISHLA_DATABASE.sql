BEGIN;

CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE EXTENSION IF NOT EXISTS citext;
CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE EXTENSION IF NOT EXISTS unaccent;

CREATE SCHEMA IF NOT EXISTS store;
CREATE SCHEMA IF NOT EXISTS auth;
CREATE SCHEMA IF NOT EXISTS audit;

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_status') THEN
        CREATE TYPE auth.user_status AS ENUM ('pending','active','suspended','disabled');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'customer_status') THEN
        CREATE TYPE store.customer_status AS ENUM ('guest','active','blocked');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'product_status') THEN
        CREATE TYPE store.product_status AS ENUM ('draft','active','archived');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'order_status') THEN
        CREATE TYPE store.order_status AS ENUM (
            'pending_payment','payment_failed','paid','confirmed','processing',
            'packed','shipped','delivered','cancelled','refunded','returned'
        );
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_status') THEN
        CREATE TYPE store.payment_status AS ENUM ('pending','authorized','captured','failed','refunded','partially_refunded');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'shipment_status') THEN
        CREATE TYPE store.shipment_status AS ENUM ('pending','label_created','picked_up','in_transit','out_for_delivery','delivered','exception','cancelled');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'inventory_movement_type') THEN
        CREATE TYPE store.inventory_movement_type AS ENUM (
            'opening','purchase','sale','sale_reversal','adjustment','damage',
            'return','reservation','reservation_release','transfer_in','transfer_out'
        );
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'return_status') THEN
        CREATE TYPE store.return_status AS ENUM ('requested','approved','rejected','received','inspected','refunded','closed');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'inquiry_status') THEN
        CREATE TYPE store.inquiry_status AS ENUM ('new','contacted','converted','closed','spam');
    END IF;
END $$;

CREATE OR REPLACE FUNCTION public.set_updated_at()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$;

CREATE SEQUENCE IF NOT EXISTS store.order_number_seq START 1;

CREATE OR REPLACE FUNCTION store.next_order_number()
RETURNS text LANGUAGE plpgsql AS $$
DECLARE
    seq bigint;
BEGIN
    SELECT nextval('store.order_number_seq') INTO seq;
    RETURN 'TIS-' || to_char(current_date, 'YYYYMM') || '-' || lpad(seq::text, 6, '0');
END;
$$;

CREATE TABLE IF NOT EXISTS store.settings (
    key text PRIMARY KEY,
    value jsonb NOT NULL DEFAULT '{}'::jsonb,
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS auth.users (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    email citext NOT NULL UNIQUE,
    password_hash text NOT NULL,
    full_name text NOT NULL,
    status auth.user_status NOT NULL DEFAULT 'pending',
    last_login_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS auth.roles (
    id smallserial PRIMARY KEY,
    code text NOT NULL UNIQUE,
    description text NOT NULL
);

CREATE TABLE IF NOT EXISTS auth.user_roles (
    user_id uuid NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
    role_id smallint NOT NULL REFERENCES auth.roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

CREATE TABLE IF NOT EXISTS auth.sessions (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id uuid NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
    refresh_token_hash text NOT NULL UNIQUE,
    expires_at timestamptz NOT NULL,
    created_at timestamptz NOT NULL DEFAULT now(),
    revoked_at timestamptz
);

CREATE TABLE IF NOT EXISTS store.categories (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    parent_id uuid REFERENCES store.categories(id) ON DELETE SET NULL,
    name text NOT NULL,
    slug text NOT NULL UNIQUE,
    description text,
    is_active boolean NOT NULL DEFAULT true,
    sort_order integer NOT NULL DEFAULT 0,
    seo_title text,
    seo_description text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.brands (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name text NOT NULL UNIQUE,
    slug text NOT NULL UNIQUE,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.products (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    sku text NOT NULL UNIQUE,
    slug text NOT NULL UNIQUE,
    name text NOT NULL,
    short_description text,
    description text,
    category_id uuid REFERENCES store.categories(id) ON DELETE SET NULL,
    brand_id uuid REFERENCES store.brands(id) ON DELETE SET NULL,
    fabric text,
    product_type text,
    shoot_type text,
    base_price numeric(12,2),
    currency char(3) NOT NULL DEFAULT 'INR',
    tax_code text,
    gst_rate numeric(5,2) NOT NULL DEFAULT 0 CHECK (gst_rate >= 0 AND gst_rate <= 100),
    status store.product_status NOT NULL DEFAULT 'draft',
    featured boolean NOT NULL DEFAULT false,
    min_order_qty integer NOT NULL DEFAULT 1 CHECK (min_order_qty > 0),
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    search_document tsvector GENERATED ALWAYS AS (
        to_tsvector('simple',
            coalesce(name,'') || ' ' ||
            coalesce(sku,'') || ' ' ||
            coalesce(fabric,'') || ' ' ||
            coalesce(product_type,'') || ' ' ||
            coalesce(shoot_type,'') || ' ' ||
            coalesce(short_description,'') || ' ' ||
            coalesce(description,'')
        )
    ) STORED,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.product_variants (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id uuid NOT NULL REFERENCES store.products(id) ON DELETE CASCADE,
    sku text NOT NULL UNIQUE,
    name text NOT NULL,
    color_name text,
    color_hex text,
    size_name text,
    barcode text,
    price numeric(12,2),
    compare_at_price numeric(12,2),
    cost_price numeric(12,2),
    weight_grams numeric(10,2),
    is_active boolean NOT NULL DEFAULT true,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    UNIQUE (product_id, name)
);

CREATE TABLE IF NOT EXISTS store.media_assets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    storage_provider text NOT NULL DEFAULT 'external_url',
    object_key text,
    public_url text NOT NULL,
    mime_type text,
    width integer,
    height integer,
    file_size_bytes bigint,
    checksum_sha256 text,
    alt_text text,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.product_images (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id uuid NOT NULL REFERENCES store.products(id) ON DELETE CASCADE,
    variant_id uuid REFERENCES store.product_variants(id) ON DELETE CASCADE,
    media_asset_id uuid NOT NULL REFERENCES store.media_assets(id) ON DELETE RESTRICT,
    sort_order integer NOT NULL DEFAULT 0,
    is_primary boolean NOT NULL DEFAULT false,
    image_code text,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.attribute_definitions (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    code text NOT NULL UNIQUE,
    label text NOT NULL,
    data_type text NOT NULL CHECK (data_type IN ('text','number','boolean','json')),
    is_filterable boolean NOT NULL DEFAULT false,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.product_attributes (
    product_id uuid NOT NULL REFERENCES store.products(id) ON DELETE CASCADE,
    attribute_id uuid NOT NULL REFERENCES store.attribute_definitions(id) ON DELETE CASCADE,
    value_text text,
    value_number numeric,
    value_boolean boolean,
    value_json jsonb,
    PRIMARY KEY (product_id, attribute_id),
    CHECK (
        num_nonnulls(value_text, value_number, value_boolean, value_json) = 1
    )
);

CREATE TABLE IF NOT EXISTS store.price_lists (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    code text NOT NULL UNIQUE,
    name text NOT NULL,
    customer_type text NOT NULL CHECK (customer_type IN ('retail','wholesale','custom')),
    priority integer NOT NULL DEFAULT 100,
    currency char(3) NOT NULL DEFAULT 'INR',
    is_active boolean NOT NULL DEFAULT true,
    starts_at timestamptz,
    ends_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.variant_prices (
    price_list_id uuid NOT NULL REFERENCES store.price_lists(id) ON DELETE CASCADE,
    variant_id uuid NOT NULL REFERENCES store.product_variants(id) ON DELETE CASCADE,
    price numeric(12,2) NOT NULL CHECK (price >= 0),
    compare_at_price numeric(12,2),
    PRIMARY KEY (price_list_id, variant_id)
);

CREATE TABLE IF NOT EXISTS store.inventory_locations (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    code text NOT NULL UNIQUE,
    name text NOT NULL,
    address text,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.inventory_stock (
    location_id uuid NOT NULL REFERENCES store.inventory_locations(id) ON DELETE CASCADE,
    variant_id uuid NOT NULL REFERENCES store.product_variants(id) ON DELETE CASCADE,
    on_hand integer NOT NULL DEFAULT 0 CHECK (on_hand >= 0),
    reserved integer NOT NULL DEFAULT 0 CHECK (reserved >= 0 AND reserved <= on_hand),
    reorder_level integer NOT NULL DEFAULT 0 CHECK (reorder_level >= 0),
    updated_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (location_id, variant_id)
);

CREATE TABLE IF NOT EXISTS store.inventory_movements (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    location_id uuid NOT NULL REFERENCES store.inventory_locations(id) ON DELETE RESTRICT,
    variant_id uuid NOT NULL REFERENCES store.product_variants(id) ON DELETE RESTRICT,
    movement_type store.inventory_movement_type NOT NULL,
    quantity integer NOT NULL,
    reference_type text,
    reference_id uuid,
    reason text,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_by uuid REFERENCES auth.users(id) ON DELETE SET NULL,
    created_at timestamptz NOT NULL DEFAULT now(),
    CHECK (quantity <> 0)
);

CREATE TABLE IF NOT EXISTS store.customers (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    email citext UNIQUE,
    phone text,
    first_name text,
    last_name text,
    company_name text,
    gstin text,
    customer_type text NOT NULL DEFAULT 'retail' CHECK (customer_type IN ('retail','wholesale')),
    status store.customer_status NOT NULL DEFAULT 'active',
    notes text,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.wholesale_accounts (
    customer_id uuid PRIMARY KEY REFERENCES store.customers(id) ON DELETE CASCADE,
    approval_status text NOT NULL DEFAULT 'pending' CHECK (approval_status IN ('pending','approved','rejected','suspended')),
    price_list_id uuid REFERENCES store.price_lists(id) ON DELETE SET NULL,
    credit_limit numeric(14,2),
    payment_terms_days integer NOT NULL DEFAULT 0,
    approved_at timestamptz,
    approved_by uuid REFERENCES auth.users(id) ON DELETE SET NULL,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.customer_addresses (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id uuid REFERENCES store.customers(id) ON DELETE CASCADE,
    label text,
    full_name text,
    phone text,
    line1 text NOT NULL,
    line2 text,
    landmark text,
    city text NOT NULL,
    state text NOT NULL,
    postal_code text NOT NULL,
    country_code char(2) NOT NULL DEFAULT 'IN',
    is_default boolean NOT NULL DEFAULT false,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.carts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id uuid REFERENCES store.customers(id) ON DELETE SET NULL,
    session_key text UNIQUE,
    currency char(3) NOT NULL DEFAULT 'INR',
    last_activity_at timestamptz NOT NULL DEFAULT now(),
    expires_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.cart_items (
    cart_id uuid NOT NULL REFERENCES store.carts(id) ON DELETE CASCADE,
    variant_id uuid NOT NULL REFERENCES store.product_variants(id) ON DELETE RESTRICT,
    quantity integer NOT NULL CHECK (quantity > 0),
    unit_price numeric(12,2),
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (cart_id, variant_id)
);

CREATE TABLE IF NOT EXISTS store.orders (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    order_number text NOT NULL UNIQUE DEFAULT store.next_order_number(),
    customer_id uuid REFERENCES store.customers(id) ON DELETE SET NULL,
    status store.order_status NOT NULL DEFAULT 'pending_payment',
    currency char(3) NOT NULL DEFAULT 'INR',
    subtotal numeric(14,2) NOT NULL DEFAULT 0,
    discount_total numeric(14,2) NOT NULL DEFAULT 0,
    taxable_total numeric(14,2) NOT NULL DEFAULT 0,
    tax_total numeric(14,2) NOT NULL DEFAULT 0,
    shipping_total numeric(14,2) NOT NULL DEFAULT 0,
    grand_total numeric(14,2) NOT NULL DEFAULT 0,
    customer_name text NOT NULL,
    customer_email citext,
    customer_phone text,
    billing_address jsonb,
    shipping_address jsonb NOT NULL,
    notes text,
    source text NOT NULL DEFAULT 'web',
    payment_method text,
    external_payment_reference text,
    external_checkout_reference text,
    placed_at timestamptz NOT NULL DEFAULT now(),
    confirmed_at timestamptz,
    cancelled_at timestamptz,
    delivered_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.order_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id uuid NOT NULL REFERENCES store.orders(id) ON DELETE CASCADE,
    product_id uuid REFERENCES store.products(id) ON DELETE SET NULL,
    variant_id uuid REFERENCES store.product_variants(id) ON DELETE SET NULL,
    sku text NOT NULL,
    product_name text NOT NULL,
    variant_name text,
    quantity integer NOT NULL CHECK (quantity > 0),
    unit_price numeric(12,2) NOT NULL CHECK (unit_price >= 0),
    discount_total numeric(12,2) NOT NULL DEFAULT 0,
    taxable_total numeric(12,2) NOT NULL DEFAULT 0,
    tax_rate numeric(5,2) NOT NULL DEFAULT 0,
    tax_total numeric(12,2) NOT NULL DEFAULT 0,
    line_total numeric(12,2) NOT NULL DEFAULT 0,
    image_url text,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.order_status_history (
    id bigserial PRIMARY KEY,
    order_id uuid NOT NULL REFERENCES store.orders(id) ON DELETE CASCADE,
    from_status store.order_status,
    to_status store.order_status NOT NULL,
    note text,
    changed_by uuid REFERENCES auth.users(id) ON DELETE SET NULL,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.payments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id uuid NOT NULL REFERENCES store.orders(id) ON DELETE CASCADE,
    provider text NOT NULL,
    provider_payment_id text,
    provider_order_id text,
    amount numeric(14,2) NOT NULL CHECK (amount >= 0),
    currency char(3) NOT NULL DEFAULT 'INR',
    status store.payment_status NOT NULL DEFAULT 'pending',
    method text,
    payload jsonb NOT NULL DEFAULT '{}'::jsonb,
    paid_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.shipments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id uuid NOT NULL REFERENCES store.orders(id) ON DELETE CASCADE,
    provider text,
    tracking_number text,
    label_url text,
    status store.shipment_status NOT NULL DEFAULT 'pending',
    shipping_method text,
    shipping_cost numeric(12,2) NOT NULL DEFAULT 0,
    payload jsonb NOT NULL DEFAULT '{}'::jsonb,
    shipped_at timestamptz,
    delivered_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.returns (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id uuid NOT NULL REFERENCES store.orders(id) ON DELETE RESTRICT,
    customer_id uuid REFERENCES store.customers(id) ON DELETE SET NULL,
    status store.return_status NOT NULL DEFAULT 'requested',
    reason text NOT NULL,
    customer_note text,
    staff_note text,
    refund_amount numeric(14,2),
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.return_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    return_id uuid NOT NULL REFERENCES store.returns(id) ON DELETE CASCADE,
    order_item_id uuid NOT NULL REFERENCES store.order_items(id) ON DELETE RESTRICT,
    quantity integer NOT NULL CHECK (quantity > 0),
    condition text,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.coupons (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    code text NOT NULL UNIQUE,
    description text,
    discount_type text NOT NULL CHECK (discount_type IN ('percent','fixed')),
    discount_value numeric(12,2) NOT NULL CHECK (discount_value >= 0),
    min_order_value numeric(12,2) NOT NULL DEFAULT 0,
    max_discount_value numeric(12,2),
    usage_limit integer,
    used_count integer NOT NULL DEFAULT 0,
    starts_at timestamptz,
    ends_at timestamptz,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.coupon_redemptions (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    coupon_id uuid NOT NULL REFERENCES store.coupons(id) ON DELETE RESTRICT,
    customer_id uuid REFERENCES store.customers(id) ON DELETE SET NULL,
    order_id uuid REFERENCES store.orders(id) ON DELETE SET NULL,
    discount_amount numeric(12,2) NOT NULL DEFAULT 0,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.enquiries (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id uuid REFERENCES store.customers(id) ON DELETE SET NULL,
    product_id uuid REFERENCES store.products(id) ON DELETE SET NULL,
    variant_id uuid REFERENCES store.product_variants(id) ON DELETE SET NULL,
    name text,
    phone text,
    email citext,
    message text,
    source text NOT NULL DEFAULT 'whatsapp',
    status store.inquiry_status NOT NULL DEFAULT 'new',
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.tax_rates (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    country_code char(2) NOT NULL DEFAULT 'IN',
    state_code text,
    tax_name text NOT NULL,
    rate numeric(5,2) NOT NULL CHECK (rate >= 0 AND rate <= 100),
    is_inclusive boolean NOT NULL DEFAULT false,
    effective_from date NOT NULL DEFAULT current_date,
    effective_to date,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS store.seo_pages (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    entity_type text NOT NULL,
    entity_id uuid,
    slug text NOT NULL UNIQUE,
    title text,
    description text,
    canonical_url text,
    robots text DEFAULT 'index,follow',
    schema_json jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS audit.audit_log (
    id bigserial PRIMARY KEY,
    actor_user_id uuid REFERENCES auth.users(id) ON DELETE SET NULL,
    action text NOT NULL,
    entity_type text NOT NULL,
    entity_id uuid,
    before_data jsonb,
    after_data jsonb,
    ip_address inet,
    user_agent text,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_products_status ON store.products(status);
CREATE INDEX IF NOT EXISTS idx_products_fabric ON store.products(fabric);
CREATE INDEX IF NOT EXISTS idx_products_shoot ON store.products(shoot_type);
CREATE INDEX IF NOT EXISTS idx_products_search_gin ON store.products USING gin(search_document);
CREATE INDEX IF NOT EXISTS idx_products_name_trgm ON store.products USING gin(name gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_variants_product ON store.product_variants(product_id);
CREATE INDEX IF NOT EXISTS idx_images_product_sort ON store.product_images(product_id, sort_order);
CREATE INDEX IF NOT EXISTS idx_inventory_low_stock ON store.inventory_stock(on_hand, reserved);
CREATE INDEX IF NOT EXISTS idx_orders_customer_created ON store.orders(customer_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_orders_status_created ON store.orders(status, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_order_items_order ON store.order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_shipments_tracking ON store.shipments(tracking_number);
CREATE INDEX IF NOT EXISTS idx_enquiries_status_created ON store.enquiries(status, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_audit_entity ON audit.audit_log(entity_type, entity_id, created_at DESC);

CREATE OR REPLACE VIEW store.v_variant_inventory AS
SELECT
    s.location_id,
    l.code AS location_code,
    l.name AS location_name,
    s.variant_id,
    v.sku AS variant_sku,
    p.id AS product_id,
    p.sku AS product_sku,
    p.name AS product_name,
    s.on_hand,
    s.reserved,
    (s.on_hand - s.reserved) AS available_quantity,
    s.reorder_level,
    (s.on_hand - s.reserved) <= s.reorder_level AS is_low_stock
FROM store.inventory_stock s
JOIN store.inventory_locations l ON l.id = s.location_id
JOIN store.product_variants v ON v.id = s.variant_id
JOIN store.products p ON p.id = v.product_id;

CREATE OR REPLACE VIEW store.v_catalogue_products AS
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
    p.min_order_qty,
    p.status,
    c.name AS category_name,
    b.name AS brand_name,
    COALESCE(
        jsonb_agg(
            DISTINCT jsonb_build_object(
                'id', v.id,
                'sku', v.sku,
                'name', v.name,
                'color_name', v.color_name,
                'color_hex', v.color_hex,
                'price', COALESCE(vp.price, v.price, p.base_price),
                'compare_at_price', COALESCE(vp.compare_at_price, v.compare_at_price),
                'available', COALESCE(inv.available_quantity, 0)
            )
        ) FILTER (WHERE v.id IS NOT NULL),
        '[]'::jsonb
    ) AS variants,
    COALESCE(
        jsonb_agg(
            DISTINCT jsonb_build_object(
                'id', pa.id,
                'url', ma.public_url,
                'sort_order', pa.sort_order,
                'is_primary', pa.is_primary,
                'variant_id', pa.variant_id,
                'image_code', pa.image_code,
                'alt_text', ma.alt_text
            )
        ) FILTER (WHERE pa.id IS NOT NULL),
        '[]'::jsonb
    ) AS images
FROM store.products p
LEFT JOIN store.categories c ON c.id = p.category_id
LEFT JOIN store.brands b ON b.id = p.brand_id
LEFT JOIN store.product_variants v ON v.product_id = p.id AND v.is_active = true
LEFT JOIN store.price_lists pl ON pl.code = 'RETAIL' AND pl.is_active = true
LEFT JOIN store.variant_prices vp ON vp.variant_id = v.id AND vp.price_list_id = pl.id
LEFT JOIN LATERAL (
    SELECT SUM(iv.available_quantity)::integer AS available_quantity
    FROM store.v_variant_inventory iv
    WHERE iv.variant_id = v.id
) inv ON true
LEFT JOIN store.product_images pa ON pa.product_id = p.id
LEFT JOIN store.media_assets ma ON ma.id = pa.media_asset_id
GROUP BY p.id, p.sku, p.slug, p.name, p.short_description, p.description, p.fabric,
         p.product_type, p.shoot_type, p.base_price, p.currency, p.gst_rate,
         p.featured, p.min_order_qty, p.status, c.name, b.name;

CREATE OR REPLACE VIEW store.v_order_totals AS
SELECT
    o.id,
    o.order_number,
    o.status,
    o.customer_name,
    o.customer_phone,
    o.customer_email,
    o.subtotal,
    o.discount_total,
    o.tax_total,
    o.shipping_total,
    o.grand_total,
    COUNT(oi.id) AS line_count,
    COALESCE(SUM(oi.quantity),0) AS item_count,
    o.created_at,
    o.placed_at
FROM store.orders o
LEFT JOIN store.order_items oi ON oi.order_id = o.id
GROUP BY o.id;

CREATE OR REPLACE VIEW store.v_customer_lifetime_value AS
SELECT
    c.id AS customer_id,
    c.email,
    c.phone,
    c.first_name,
    c.last_name,
    c.customer_type,
    COUNT(o.id) FILTER (WHERE o.status NOT IN ('cancelled','payment_failed')) AS order_count,
    COALESCE(SUM(o.grand_total) FILTER (WHERE o.status NOT IN ('cancelled','payment_failed')),0) AS lifetime_value,
    MAX(o.created_at) AS last_order_at
FROM store.customers c
LEFT JOIN store.orders o ON o.customer_id = c.id
GROUP BY c.id;

CREATE OR REPLACE VIEW store.v_admin_dashboard AS
SELECT
    (SELECT COUNT(*) FROM store.products WHERE status = 'active') AS active_products,
    (SELECT COUNT(*) FROM store.customers WHERE status = 'active') AS active_customers,
    (SELECT COUNT(*) FROM store.orders WHERE created_at >= now() - interval '30 days') AS orders_30d,
    (SELECT COALESCE(SUM(grand_total),0) FROM store.orders WHERE created_at >= now() - interval '30 days' AND status NOT IN ('cancelled','payment_failed')) AS revenue_30d,
    (SELECT COUNT(*) FROM store.enquiries WHERE status = 'new') AS new_enquiries,
    (SELECT COUNT(*) FROM store.v_variant_inventory WHERE is_low_stock) AS low_stock_variants;

CREATE OR REPLACE FUNCTION store.log_order_status_change()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.status IS DISTINCT FROM OLD.status THEN
        INSERT INTO store.order_status_history(order_id, from_status, to_status)
        VALUES (NEW.id, OLD.status, NEW.status);
    END IF;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_order_status_history ON store.orders;
CREATE TRIGGER trg_order_status_history
AFTER UPDATE OF status ON store.orders
FOR EACH ROW EXECUTE FUNCTION store.log_order_status_change();

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM auth.roles WHERE code = 'admin') THEN
        INSERT INTO auth.roles(code, description) VALUES ('admin','Full store administration');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM auth.roles WHERE code = 'catalogue_manager') THEN
        INSERT INTO auth.roles(code, description) VALUES ('catalogue_manager','Catalogue and inventory management');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM auth.roles WHERE code = 'order_manager') THEN
        INSERT INTO auth.roles(code, description) VALUES ('order_manager','Order, shipment and return management');
    END IF;

    INSERT INTO store.settings(key, value)
    VALUES
      ('store', jsonb_build_object(
        'name','Tishla by Purnika Sales',
        'domain','https://www.tishla.com',
        'currency','INR',
        'country','IN',
        'state','Gujarat',
        'city','Surat',
        'support_whatsapp','919574716712',
        'support_email','purnikasales@gmail.com'
      )),
      ('checkout', jsonb_build_object(
        'guest_checkout',true,
        'cod_enabled',true,
        'online_payment_enabled',false,
        'minimum_order_value',0
      ))
    ON CONFLICT (key) DO NOTHING;

    INSERT INTO store.price_lists(code,name,customer_type,priority)
    VALUES
      ('RETAIL','Retail','retail',100),
      ('WHOLESALE','Wholesale','wholesale',20)
    ON CONFLICT (code) DO NOTHING;

    INSERT INTO store.inventory_locations(code,name,address)
    VALUES ('SURAT_MAIN','Surat Main Warehouse','Surat, Gujarat, India')
    ON CONFLICT (code) DO NOTHING;
END $$;

DROP TRIGGER IF EXISTS trg_settings_updated ON store.settings;
CREATE TRIGGER trg_settings_updated BEFORE UPDATE ON store.settings
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_users_updated ON auth.users;
CREATE TRIGGER trg_users_updated BEFORE UPDATE ON auth.users
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_categories_updated ON store.categories;
CREATE TRIGGER trg_categories_updated BEFORE UPDATE ON store.categories
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_brands_updated ON store.brands;
CREATE TRIGGER trg_brands_updated BEFORE UPDATE ON store.brands
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_products_updated ON store.products;
CREATE TRIGGER trg_products_updated BEFORE UPDATE ON store.products
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_variants_updated ON store.product_variants;
CREATE TRIGGER trg_variants_updated BEFORE UPDATE ON store.product_variants
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_media_updated ON store.media_assets;
CREATE TRIGGER trg_media_updated BEFORE UPDATE ON store.media_assets
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_prices_updated ON store.price_lists;
CREATE TRIGGER trg_prices_updated BEFORE UPDATE ON store.price_lists
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_customers_updated ON store.customers;
CREATE TRIGGER trg_customers_updated BEFORE UPDATE ON store.customers
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_wholesale_updated ON store.wholesale_accounts;
CREATE TRIGGER trg_wholesale_updated BEFORE UPDATE ON store.wholesale_accounts
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_addresses_updated ON store.customer_addresses;
CREATE TRIGGER trg_addresses_updated BEFORE UPDATE ON store.customer_addresses
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_carts_updated ON store.carts;
CREATE TRIGGER trg_carts_updated BEFORE UPDATE ON store.carts
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_cart_items_updated ON store.cart_items;
CREATE TRIGGER trg_cart_items_updated BEFORE UPDATE ON store.cart_items
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_orders_updated ON store.orders;
CREATE TRIGGER trg_orders_updated BEFORE UPDATE ON store.orders
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_payments_updated ON store.payments;
CREATE TRIGGER trg_payments_updated BEFORE UPDATE ON store.payments
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_shipments_updated ON store.shipments;
CREATE TRIGGER trg_shipments_updated BEFORE UPDATE ON store.shipments
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_returns_updated ON store.returns;
CREATE TRIGGER trg_returns_updated BEFORE UPDATE ON store.returns
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_coupons_updated ON store.coupons;
CREATE TRIGGER trg_coupons_updated BEFORE UPDATE ON store.coupons
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_enquiries_updated ON store.enquiries;
CREATE TRIGGER trg_enquiries_updated BEFORE UPDATE ON store.enquiries
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

DROP TRIGGER IF EXISTS trg_seo_updated ON store.seo_pages;
CREATE TRIGGER trg_seo_updated BEFORE UPDATE ON store.seo_pages
FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE OR REPLACE FUNCTION store.refresh_inventory_stock(
    p_location_id uuid,
    p_variant_id uuid
) RETURNS void LANGUAGE plpgsql AS $$
DECLARE
    v_on_hand integer;
BEGIN
    SELECT COALESCE(SUM(
        CASE
            WHEN movement_type IN ('opening','purchase','sale_reversal','adjustment','return','transfer_in') THEN quantity
            WHEN movement_type IN ('sale','damage','transfer_out') THEN -quantity
            ELSE 0
        END
    ),0)
    INTO v_on_hand
    FROM store.inventory_movements
    WHERE location_id = p_location_id AND variant_id = p_variant_id;

    INSERT INTO store.inventory_stock(location_id,variant_id,on_hand,reserved)
    VALUES(p_location_id,p_variant_id,GREATEST(v_on_hand,0),0)
    ON CONFLICT(location_id,variant_id)
    DO UPDATE SET on_hand = GREATEST(EXCLUDED.on_hand,0), updated_at=now();
END;
$$;

COMMIT;


BEGIN;

CREATE TABLE IF NOT EXISTS store.catalogue_import_batches (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    source_filename text NOT NULL,
    source_type text NOT NULL DEFAULT 'csv',
    source_checksum_sha256 text,
    mode text NOT NULL DEFAULT 'preview' CHECK (mode IN ('preview','apply')),
    status text NOT NULL DEFAULT 'started' CHECK (status IN ('started','previewed','completed','completed_with_errors','failed')),
    total_rows integer NOT NULL DEFAULT 0,
    valid_rows integer NOT NULL DEFAULT 0,
    created_products integer NOT NULL DEFAULT 0,
    updated_products integer NOT NULL DEFAULT 0,
    created_variants integer NOT NULL DEFAULT 0,
    updated_variants integer NOT NULL DEFAULT 0,
    created_media integer NOT NULL DEFAULT 0,
    reused_media integer NOT NULL DEFAULT 0,
    created_images integer NOT NULL DEFAULT 0,
    skipped_rows integer NOT NULL DEFAULT 0,
    error_rows integer NOT NULL DEFAULT 0,
    warning_rows integer NOT NULL DEFAULT 0,
    started_at timestamptz NOT NULL DEFAULT now(),
    completed_at timestamptz,
    metadata jsonb NOT NULL DEFAULT '{}'::jsonb
);

CREATE TABLE IF NOT EXISTS store.catalogue_import_issues (
    id bigserial PRIMARY KEY,
    batch_id uuid NOT NULL REFERENCES store.catalogue_import_batches(id) ON DELETE CASCADE,
    row_number integer NOT NULL,
    severity text NOT NULL CHECK (severity IN ('error','warning','info')),
    issue_code text NOT NULL,
    message text NOT NULL,
    raw_row jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_catalogue_import_batches_status
    ON store.catalogue_import_batches(status, started_at DESC);

CREATE INDEX IF NOT EXISTS idx_catalogue_import_issues_batch
    ON store.catalogue_import_issues(batch_id, severity, row_number);

CREATE INDEX IF NOT EXISTS idx_media_assets_checksum
    ON store.media_assets(checksum_sha256)
    WHERE checksum_sha256 IS NOT NULL;

INSERT INTO store.price_lists(code,name,customer_type,priority,currency,is_active)
VALUES
    ('RETAIL','Retail Prices','retail',10,'INR',true),
    ('WHOLESALE','Wholesale Prices','wholesale',20,'INR',true)
ON CONFLICT (code) DO UPDATE
SET name=EXCLUDED.name,
    customer_type=EXCLUDED.customer_type,
    priority=EXCLUDED.priority,
    currency=EXCLUDED.currency,
    is_active=EXCLUDED.is_active;

COMMIT;


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

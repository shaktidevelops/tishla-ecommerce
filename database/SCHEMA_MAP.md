# Tishla database map

## Schemas

### auth
Users, roles, role assignments and refresh sessions.

### store
Commerce source of truth: settings, departments, categories, brands, products, variants, media, attributes, price lists, inventory, customers, wholesale accounts, addresses, carts, orders, payments, shipments, returns, coupons, enquiries, tax rates, SEO, wishlists, recently viewed and reviews.

### cms
Editable storefront content: pages, banners, homepage sections, navigation menus/items, lookbooks, lookbook items, blog posts and FAQs.

### audit
Immutable operational audit records.

## Core relationship

products → product_variants → variant_prices → inventory_stock → cart_items → order_items.

products → collection_products → collections.
products → department_id → departments.
products → product_images → media_assets.
customers → wholesale_accounts / addresses / wishlists / carts / orders.

## Storefront views

`store.v_storefront_products` is the main catalogue view used by the API.
`cms.v_navigation_items` provides a menu-ready navigation projection.
Existing commerce views such as `store.v_order_totals` and `store.v_admin_dashboard` remain available from the core migration.

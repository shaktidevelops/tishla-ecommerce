# Tishla Frontend Architecture

The storefront is a Next.js App Router application with React and TypeScript. It is intentionally multi-page and commerce-first.

## Information architecture

Home, Shop, Departments, Collections, Product Detail, Search, Wishlist, Cart, Checkout, Account, Account Orders, Wholesale, Lookbook, Journal, About, Contact, Track Order, Shipping, Returns, FAQ, Size Guide, and Admin sections are included.

## Navigation

The desktop header has primary navigation plus hover mega menus. Mobile uses a dedicated drawer and a compact header.

## Visual system

The existing Tishla wine/burgundy, zari-gold and ivory palette is retained, with editorial photography, fabric-like texture panels, large imagery, serif display typography, restrained iconography and responsive layouts.

## Data boundary

Demo data is kept in the frontend for the first visual pass. `NEXT_PUBLIC_API_BASE_URL` can point to the Python API; the API already exposes storefront product, department, collection and navigation endpoints. The frontend is therefore able to transition from demo data to PostgreSQL-backed content without changing the page architecture.

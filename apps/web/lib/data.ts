export type Product = {
  slug: string;
  sku: string;
  name: string;
  price: number;
  compareAtPrice?: number;
  fabric: string;
  color: string;
  badge?: string;
  rating: number;
  reviews: number;
  image: string;
  hoverImage: string;
  category: string;
  department: string;
};

export const products: Product[] = [
  { slug: "noor-dola-silk-saree", sku: "TS-NOOR-001", name: "Noor Dola Silk Saree", price: 2499, compareAtPrice: 2999, fabric: "Dola Silk", color: "Wine", badge: "Bestseller", rating: 4.9, reviews: 128, image: "https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=900&q=85", category: "Sarees", department: "Sarees" },
  { slug: "zari-heritage-silk", sku: "TS-HER-002", name: "Zari Heritage Silk Saree", price: 3899, compareAtPrice: 4499, fabric: "Pure Silk", color: "Crimson", badge: "New", rating: 4.8, reviews: 86, image: "https://images.unsplash.com/photo-1603252110481-7ba873bf42ab?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=900&q=85", category: "Sarees", department: "Sarees" },
  { slug: "petal-organza-party-set", sku: "TS-PET-003", name: "Petal Organza Party Saree", price: 2799, compareAtPrice: 3299, fabric: "Organza", color: "Rose", badge: "Trending", rating: 4.7, reviews: 74, image: "https://images.unsplash.com/photo-1583391733956-6c78276477e2?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=900&q=85", category: "Sarees", department: "Sarees" },
  { slug: "aurora-georgette-drape", sku: "TS-AUR-004", name: "Aurora Georgette Drape", price: 2199, compareAtPrice: 2699, fabric: "Georgette", color: "Emerald", badge: "New", rating: 4.8, reviews: 61, image: "https://images.unsplash.com/photo-1621784563330-caee0b138a00?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=900&q=85", category: "Sarees", department: "Sarees" },
  { slug: "royal-brocade-lehenga", sku: "TS-RBL-005", name: "Royal Brocade Lehenga Set", price: 6499, compareAtPrice: 7999, fabric: "Brocade", color: "Gold", badge: "Exclusive", rating: 4.9, reviews: 39, image: "https://images.unsplash.com/photo-1610189012906-5e132fbbc77f?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1610030469668-8a7b2e0b5f95?auto=format&fit=crop&w=900&q=85", category: "Lehengas", department: "Lehengas" },
  { slug: "marigold-embroidered-kurti-set", sku: "TS-MAR-006", name: "Marigold Embroidered Kurti Set", price: 1899, compareAtPrice: 2299, fabric: "Viscose", color: "Marigold", badge: "Best Seller", rating: 4.7, reviews: 143, image: "https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=900&q=85", category: "Kurtis & Sets", department: "Kurtis & Sets" },
  { slug: "ivory-evening-gown", sku: "TS-GWN-007", name: "Ivory Evening Gown", price: 4999, compareAtPrice: 5999, fabric: "Crepe", color: "Ivory", badge: "Limited", rating: 4.8, reviews: 42, image: "https://images.unsplash.com/photo-1539008835657-9e8e9680c956?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=900&q=85", category: "Gowns", department: "Gowns" },
  { slug: "midnight-sequin-suit", sku: "TS-SUT-008", name: "Midnight Sequin Suit", price: 3199, compareAtPrice: 3799, fabric: "Georgette", color: "Midnight", badge: "Party Edit", rating: 4.6, reviews: 54, image: "https://images.unsplash.com/photo-1591369822096-ffd140ec948f?auto=format&fit=crop&w=900&q=85", hoverImage: "https://images.unsplash.com/photo-1564257631407-4deb1f99d992?auto=format&fit=crop&w=900&q=85", category: "Suits", department: "Suits" },
];

export const departments = [
  { name: "Sarees", slug: "sarees", description: "Silk, organza, georgette and statement drapes." },
  { name: "Lehengas", slug: "lehengas", description: "Celebration-ready sets for wedding season." },
  { name: "Kurtis & Sets", slug: "kurtis-sets", description: "Polished everyday and festive coordinates." },
  { name: "Suits", slug: "suits", description: "Tailored silhouettes with occasion-ready detail." },
  { name: "Gowns", slug: "gowns", description: "Fluid evening shapes and statement dressing." },
  { name: "Dupattas", slug: "dupattas", description: "Layering pieces with luxe texture." },
  { name: "Blouses", slug: "blouses", description: "Designed to complete the drape." },
  { name: "Accessories", slug: "accessories", description: "Finishing touches for the full look." },
];

export const collections = [
  { name: "New Arrivals", slug: "new-arrivals", kicker: "JUST IN", description: "Fresh silhouettes, colours and textures for the new season." },
  { name: "Bestsellers", slug: "bestsellers", kicker: "MOST LOVED", description: "Signature Tishla styles customers keep coming back for." },
  { name: "Wedding Edit", slug: "wedding-edit", kicker: "THE CELEBRATION", description: "Silk, zari and statement dressing for every wedding event." },
  { name: "Festive Edit", slug: "festive-edit", kicker: "FESTIVE", description: "Rich colour, shimmer and craftsmanship for the season." },
  { name: "Party Edit", slug: "party-edit", kicker: "AFTER DARK", description: "Sequins, shimmer and evening-ready silhouettes." },
  { name: "Ready to Ship", slug: "ready-to-ship", kicker: "FAST DISPATCH", description: "Curated pieces available for quick dispatch." },
  { name: "Sale", slug: "sale", kicker: "SPECIAL EDIT", description: "Special pricing on selected Tishla pieces." },
];

"use client";

import { useMemo, useState } from "react";
import { SlidersHorizontal, ArrowUpDown, Search } from "lucide-react";
import { ProductCard } from "./product-card";
import type { Product } from "@/lib/data";

export function ProductGridClient({ products, emptyText = "No products match these filters." }: { products: Product[]; emptyText?: string }) {
  const [search, setSearch] = useState("");
  const [fabric, setFabric] = useState("all");
  const [color, setColor] = useState("all");
  const [sort, setSort] = useState("featured");

  const fabrics = useMemo(() => ["all", ...Array.from(new Set(products.map((p) => p.fabric)))], [products]);
  const colors = useMemo(() => ["all", ...Array.from(new Set(products.map((p) => p.color)))], [products]);

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase();
    const items = products.filter((p) => {
      const matchesSearch = !q || `${p.name} ${p.sku} ${p.fabric} ${p.department} ${p.color}`.toLowerCase().includes(q);
      return matchesSearch && (fabric === "all" || p.fabric === fabric) && (color === "all" || p.color === color);
    });
    return [...items].sort((a, b) => {
      if (sort === "price-low") return a.price - b.price;
      if (sort === "price-high") return b.price - a.price;
      if (sort === "rating") return b.rating - a.rating;
      return (a.badge === "Bestseller" ? -1 : 0) - (b.badge === "Bestseller" ? -1 : 0);
    });
  }, [products, search, fabric, color, sort]);

  return <>
    <div className="listing-toolbar">
      <div className="listing-result"><strong>{filtered.length}</strong> of {products.length} styles</div>
      <div className="listing-controls">
        <label className="listing-search"><Search size={15}/><input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search this edit" aria-label="Search this edit"/></label>
        <label className="listing-select"><SlidersHorizontal size={14}/><select value={fabric} onChange={(e) => setFabric(e.target.value)} aria-label="Filter by fabric">{fabrics.map((f) => <option value={f} key={f}>{f === "all" ? "All fabrics" : f}</option>)}</select></label>
        <label className="listing-select"><select value={color} onChange={(e) => setColor(e.target.value)} aria-label="Filter by colour">{colors.map((c) => <option value={c} key={c}>{c === "all" ? "All colours" : c}</option>)}</select></label>
        <label className="listing-select"><ArrowUpDown size={14}/><select value={sort} onChange={(e) => setSort(e.target.value)} aria-label="Sort products"><option value="featured">Featured</option><option value="rating">Top rated</option><option value="price-low">Price: low to high</option><option value="price-high">Price: high to low</option></select></label>
      </div>
    </div>
    {filtered.length ? <div className="product-grid">{filtered.map((p) => <ProductCard product={p} key={p.slug}/>)}</div> : <div className="empty-state"><h3>Nothing here yet.</h3><p>{emptyText}</p><button className="button button-dark" onClick={() => { setSearch(""); setFabric("all"); setColor("all"); }}>Clear filters</button></div>}
  </>;
}

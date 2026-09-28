"use client";

import { LoaderCircle, Plus, Save, Search } from "lucide-react";
import { useEffect, useState } from "react";
import { useAdmin } from "../components/admin-provider";

type Product = {
  id: string; sku: string; slug: string; name: string; fabric: string | null; product_type: string | null; shoot_type: string | null;
  base_price: number | null; gst_rate: number; status: string; featured: boolean; min_order_qty: number; variant_count: number; image_count: number; updated_at: string;
};

const blank = { sku: "", slug: "", name: "", fabric: "", product_type: "Saree", shoot_type: "Model Shoot", base_price: "", gst_rate: "5", description: "" };

export default function CataloguePage() {
  const { api } = useAdmin();
  const [products, setProducts] = useState<Product[]>([]);
  const [query, setQuery] = useState("");
  const [status, setStatus] = useState("all");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState<string | null>(null);
  const [newProduct, setNewProduct] = useState(blank);
  const [newOpen, setNewOpen] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  async function load() {
    setLoading(true); setError("");
    try {
      const qs = new URLSearchParams(); if (query) qs.set("q", query); if (status !== "all") qs.set("status", status);
      const result = await api<{ data: Product[] }>(`/api/admin/products?${qs.toString()}`);
      setProducts(result.data || []);
    } catch (err) { setError(err instanceof Error ? err.message : "Unable to load catalogue."); }
    finally { setLoading(false); }
  }

  useEffect(() => { load(); }, [status]);

  function patch(id: string, key: keyof Product, value: unknown) { setProducts(rows => rows.map(row => row.id === id ? { ...row, [key]: value } as Product : row)); }

  async function saveProduct(product: Product) {
    setSaving(product.id); setMessage(""); setError("");
    try {
      const result = await api<{ data: Product }>(`/api/admin/products/${product.id}`, { method: "PATCH", body: JSON.stringify({ sku: product.sku, slug: product.slug, name: product.name, fabric: product.fabric, product_type: product.product_type, shoot_type: product.shoot_type, base_price: product.base_price, gst_rate: product.gst_rate, status: product.status, featured: product.featured, min_order_qty: product.min_order_qty }) });
      setProducts(rows => rows.map(row => row.id === product.id ? { ...row, ...result.data } : row));
      setMessage(`${product.sku} saved.`);
    } catch (err) { setError(err instanceof Error ? err.message : "Unable to save product."); }
    finally { setSaving(null); }
  }

  async function createProduct() {
    setMessage(""); setError("");
    try {
      await api(`/api/admin/products`, { method: "POST", body: JSON.stringify({ ...newProduct, base_price: Number(newProduct.base_price || 0), gst_rate: Number(newProduct.gst_rate || 0), description: newProduct.description || null }) });
      setNewProduct(blank); setNewOpen(false); setMessage("Product created."); await load();
    } catch (err) { setError(err instanceof Error ? err.message : "Unable to create product."); }
  }

  return <main className="admin-page">
    <div className="admin-page-head"><div><span className="eyebrow">CATALOGUE MANAGEMENT</span><h1>Products</h1><p>Edit live catalogue data directly against PostgreSQL. Variants, media and inventory are next in this module.</p></div><button className="button button-dark" onClick={() => setNewOpen(v=>!v)}><Plus size={15}/> New product</button></div>
    {(message || error) && <div className={`admin-message ${error ? "error" : "success"}`} style={{ marginBottom: 14 }}>{error || message}</div>}
    {newOpen && <section className="admin-panel" style={{ marginBottom: 18 }}><h2>Create product</h2><p className="admin-panel-sub">Create the product record first; variants and media can be attached afterward.</p><div className="admin-form-grid three">{([['sku','SKU'],['slug','Slug'],['name','Product name'],['fabric','Fabric'],['product_type','Product type'],['shoot_type','Shoot type'],['base_price','Retail price'],['gst_rate','GST %']] as const).map(([key,label])=><div className="admin-field" key={key}><label>{label}</label><input value={newProduct[key]} onChange={e=>setNewProduct(v=>({...v,[key]:e.target.value}))}/></div>)}</div><div className="admin-actions-row"><button className="button" onClick={()=>setNewOpen(false)}>Cancel</button><button className="button button-dark" onClick={createProduct}>Create product</button></div></section>}
    <section className="admin-panel"><div className="admin-toolbar" style={{ marginBottom: 16 }}><div className="listing-search" style={{ flex: 1, maxWidth: 520 }}><Search size={14}/><input value={query} onChange={e=>setQuery(e.target.value)} placeholder="Search SKU, product or fabric…" onKeyDown={e=>{if(e.key==='Enter')load()}}/></div><select className="listing-select" style={{ height: 38, border: '1px solid #ded2c6' }} value={status} onChange={e=>setStatus(e.target.value)}><option value="all">All statuses</option><option value="active">Active</option><option value="draft">Draft</option><option value="archived">Archived</option></select><button className="button" onClick={load}>Search</button></div>
      {loading ? <div className="admin-loading"><LoaderCircle className="spin"/><span>Loading products…</span></div> : products.length === 0 ? <div className="admin-message">No products found.</div> : <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>SKU</th><th>Name</th><th>Fabric</th><th>Type</th><th>Price</th><th>Status</th><th>Featured</th><th>Min Qty</th><th>Media</th><th>Save</th></tr></thead><tbody>{products.map(product=><tr key={product.id}><td><input className="admin-edit-input" value={product.sku} onChange={e=>patch(product.id,'sku',e.target.value)}/></td><td><input className="admin-edit-input" style={{minWidth:200}} value={product.name} onChange={e=>patch(product.id,'name',e.target.value)}/></td><td><input className="admin-edit-input" value={product.fabric || ""} onChange={e=>patch(product.id,'fabric',e.target.value)}/></td><td>{product.product_type || "—"}</td><td><input className="admin-edit-input" type="number" min="0" step="0.01" value={product.base_price ?? 0} onChange={e=>patch(product.id,'base_price',Number(e.target.value))}/></td><td><select className="admin-edit-input" value={product.status} onChange={e=>patch(product.id,'status',e.target.value)}><option value="active">Active</option><option value="draft">Draft</option><option value="archived">Archived</option></select></td><td><input type="checkbox" checked={product.featured} onChange={e=>patch(product.id,'featured',e.target.checked)}/></td><td><input className="admin-edit-input" style={{minWidth:70}} type="number" min="1" value={product.min_order_qty} onChange={e=>patch(product.id,'min_order_qty',Number(e.target.value))}/></td><td>{product.image_count} images · {product.variant_count} variants</td><td><button className="button button-dark" style={{minHeight:34,padding:'0 10px'}} disabled={saving===product.id} onClick={()=>saveProduct(product)}>{saving===product.id?<LoaderCircle className="spin" size={14}/>:<Save size={14}/>}</button></td></tr>)}</tbody></table></div>}
    </section>
  </main>;
}

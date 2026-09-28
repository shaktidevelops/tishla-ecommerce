"use client";

import Link from "next/link";
import { BarChart3, Image as ImageIcon, Layers3, Package, Settings, ShoppingCart, Store, Users } from "lucide-react";
import { useEffect, useState } from "react";
import type { ComponentType } from "react";
import { useAdmin } from "./components/admin-provider";

type Dashboard = { active_products?: number; active_customers?: number; orders_30d?: number; revenue_30d?: number; new_enquiries?: number; low_stock_variants?: number };

type Shortcut = [string, string, string, ComponentType<{ size?: number }>];
const shortcuts: Shortcut[] = [
  ["Catalogue", "Products, variants, pricing and stock", "/admin/catalogue", Package],
  ["Orders", "Payment, packing and fulfilment", "/admin/orders", ShoppingCart],
  ["Merchandising", "Departments, collections and homepage", "/admin/merchandising", Layers3],
  ["Media", "Brand assets and product imagery", "/admin/media", ImageIcon],
  ["Customers", "Retail and wholesale buyers", "/admin/customers", Users],
  ["Store Settings", "Brand, checkout, tax and contact", "/admin/settings", Settings],
  ["Analytics", "Sales, customers and inventory", "/admin/analytics", BarChart3],
  ["View Storefront", "Open the public Tishla website", "/", Store],
];

export default function AdminPage() {
  const { api } = useAdmin();
  const [dashboard, setDashboard] = useState<Dashboard>({});
  const [error, setError] = useState("");

  async function load() {
    try {
      setError("");
      const response = await api<{ data: Dashboard }>("/api/admin/dashboard");
      setDashboard(response.data || {});
    } catch (err) {
      setError(err instanceof Error ? err.message : "Unable to load dashboard.");
    }
  }

  useEffect(() => { load(); }, []);

  const kpis = [
    ["Active products", dashboard.active_products ?? 0],
    ["Active customers", dashboard.active_customers ?? 0],
    ["Orders · 30 days", dashboard.orders_30d ?? 0],
    ["Revenue · 30 days", `₹${Number(dashboard.revenue_30d ?? 0).toLocaleString("en-IN")}`],
    ["New enquiries", dashboard.new_enquiries ?? 0],
    ["Low-stock variants", dashboard.low_stock_variants ?? 0],
  ];

  return <main className="admin-page">
    <div className="admin-page-head">
      <div><span className="eyebrow">TISHLA CONTROL ROOM</span><h1>Good morning.</h1><p>One place to run the storefront, catalogue and commerce operations.</p></div>
      <div className="admin-toolbar"><button className="button button-dark" onClick={load}>Refresh dashboard</button></div>
    </div>
    {error && <div className="admin-message error" style={{ marginBottom: 14 }}>{error}</div>}
    <section className="admin-kpi-grid">{kpis.map(([label, value]) => <div className="admin-kpi-card" key={String(label)}><span>{label}</span><strong>{value}</strong></div>)}</section>
    <section className="admin-panel" style={{ marginBottom: 18 }}><h2>Quick access</h2><p className="admin-panel-sub">Your core operations are one click away. Content modules are being activated progressively against PostgreSQL.</p><div className="admin-shortcuts">{shortcuts.map(([label, desc, href, Icon]) => <Link href={href} className="admin-shortcut" key={href}><Icon size={18}/><b>{label}</b><span>{desc}</span></Link>)}</div></section>
  </main>;
}

"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { BarChart3, Layers3, LayoutDashboard, LogOut, Package, Settings, ShoppingCart, Users } from "lucide-react";
import { useAdmin } from "./admin-provider";

const items = [
  ["Dashboard", "/admin", LayoutDashboard],
  ["Catalogue", "/admin/catalogue", Package],
  ["Orders", "/admin/orders", ShoppingCart],
  ["Customers", "/admin/customers", Users],
  ["Merchandising", "/admin/merchandising", Layers3],
  ["Analytics", "/admin/analytics", BarChart3],
  ["Store Settings", "/admin/settings", Settings],
] as const;

export function AdminNav() {
  const pathname = usePathname();
  const { user, logout } = useAdmin();
  return (
    <aside className="admin-sidebar">
      <Link href="/admin" className="admin-side-brand"><span>TISHLA</span><small>CONTROL ROOM</small></Link>
      <div className="admin-side-user"><strong>{user?.full_name || "Admin"}</strong><span>{user?.roles?.join(" · ")}</span></div>
      <nav className="admin-side-nav">
        {items.map(([label, href, Icon]) => {
          const active = href === "/admin" ? pathname === href : pathname.startsWith(href);
          return <Link key={href} href={href} className={active ? "active" : ""}><Icon size={17}/><span>{label}</span></Link>;
        })}
      </nav>
      <div className="admin-side-bottom">
        <Link href="/" className="admin-side-store">View Storefront</Link>
        <button onClick={logout}><LogOut size={16}/> Sign out</button>
      </div>
    </aside>
  );
}

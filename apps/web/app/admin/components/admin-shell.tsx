"use client";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { LayoutDashboard, Package, ShoppingCart, Users, Layers3, BarChart3, LogOut, Store } from "lucide-react";
import { useAdmin } from "./admin-provider";
const nav=[["/admin","Dashboard",LayoutDashboard],["/admin/catalogue","Catalogue",Package],["/admin/orders","Orders",ShoppingCart],["/admin/customers","Customers",Users],["/admin/merchandising","Merchandising",Layers3],["/admin/analytics","Analytics",BarChart3]] as const;
export function AdminShell({children}:{children:React.ReactNode}){const pathname=usePathname();const {user,logout}=useAdmin();return <div className="admin-shell"><aside className="admin-sidebar"><Link href="/" className="admin-brand"><Store size={18}/><span><b>TISHLA</b><small>CONTROL ROOM</small></span></Link><nav className="admin-nav">{nav.map(([href,label,Icon])=><Link key={href} href={href} className={pathname===href?"active":""}><Icon size={17}/><span>{label}</span></Link>)}</nav><div className="admin-sidebar-bottom"><div className="admin-user"><span>{user?.full_name||"Tishla Admin"}</span><small>{user?.roles?.join(" · ")||"admin"}</small></div><button onClick={logout}><LogOut size={16}/> Sign out</button></div></aside><section className="admin-main">{children}</section></div>}

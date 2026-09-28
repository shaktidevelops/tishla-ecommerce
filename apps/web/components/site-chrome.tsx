"use client";

import { useState } from "react";
import { useCart } from "./cart-provider";
import Link from "next/link";
import Image from "next/image";
import { ChevronDown, Menu, Search, Heart, ShoppingBag, UserRound, X, Truck, Sparkles, Camera, Play, Globe2, MessageCircle } from "lucide-react";

const navGroups = [
  { label: "Shop", promo: "https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=700&q=88", items: [["All Products", "/shop"], ["Sarees", "/departments/sarees"], ["Lehengas", "/departments/lehengas"], ["Kurtis & Sets", "/departments/kurtis-sets"], ["Suits", "/departments/suits"], ["Gowns", "/departments/gowns"], ["Dupattas", "/departments/dupattas"], ["Blouses", "/departments/blouses"]] },
  { label: "Collections", promo: "https://images.unsplash.com/photo-1583391733956-6c78276477e2?auto=format&fit=crop&w=700&q=88", items: [["New Arrivals", "/collections/new-arrivals"], ["Bestsellers", "/collections/bestsellers"], ["Wedding Edit", "/collections/wedding-edit"], ["Festive Edit", "/collections/festive-edit"], ["Party Edit", "/collections/party-edit"], ["Ready to Ship", "/collections/ready-to-ship"]] },
  { label: "Occasions", promo: "https://images.unsplash.com/photo-1604014237800-1c9102c219da?auto=format&fit=crop&w=700&q=88", items: [["Wedding", "/collections/wedding-edit"], ["Bridal", "/collections/wedding-edit"], ["Reception", "/collections/wedding-edit"], ["Festive", "/collections/festive-edit"], ["Party", "/collections/party-edit"], ["Office", "/shop?style=office"], ["Casual", "/shop?style=casual"]] },
  { label: "Discover", promo: "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=700&q=88", items: [["Lookbook", "/lookbook"], ["Journal", "/journal"], ["Our Story", "/about"], ["Our Factory", "/about#factory"], ["Wholesale", "/wholesale"], ["Contact", "/contact"]] },
];

export function SiteChrome({ children }: { children: React.ReactNode }) {
  const {count}=useCart();
  const [open, setOpen] = useState<string | null>(null);
  const [mobile, setMobile] = useState(false);
  return (
    <>
      <div className="announcement"><span><Truck size={14}/> Complimentary shipping on qualifying orders</span><span className="announcement-center"><Sparkles size={14}/> New season: The Tishla Edit</span><span>Wholesale & retail orders</span></div>
      <header className="site-header">
        <div className="header-main container">
          <button className="icon-btn mobile-only" aria-label="Open menu" onClick={() => setMobile(true)}><Menu/></button>
          <Link href="/" className="brand-lockup"><Image src="/branding/logo.png" alt="Tishla by Purnika Sales" width={68} height={68}/><span><b>TISHLA</b><small>BY PURNIKA SALES</small></span></Link>
          <Link href="/search" className="desktop-tools search-mini"><Search size={18}/><span>Search our collections</span></Link>
          <div className="header-actions">
            <Link href="/search" className="icon-btn" aria-label="Search"><Search/></Link><Link href="/account" className="icon-btn" aria-label="Account"><UserRound/></Link><Link href="/wishlist" className="icon-btn" aria-label="Wishlist"><Heart/></Link><Link href="/cart" className="icon-btn cart-btn" aria-label="Cart"><ShoppingBag/><span>{count}</span></Link>
          </div>
        </div>
        <nav className="desktop-nav">
          <div className="container nav-row">
            <Link href="/new-in" className="nav-item hot">NEW IN</Link>
            {navGroups.map(group => <div key={group.label} className="nav-group" onMouseEnter={() => setOpen(group.label)} onMouseLeave={() => setOpen(null)}><button className="nav-item">{group.label}<ChevronDown size={14}/></button>{open===group.label && <div className="mega-menu"><div className="mega-inner container"><div className="mega-copy"><span className="eyebrow">TISHLA EDIT</span><h3>{group.label === "Shop" ? "Dress the moment." : group.label === "Collections" ? "A new point of view." : group.label === "Occasions" ? "For every celebration." : "Discover the world of Tishla."}</h3><p>Explore considered Indian fashion, refined fabrics and statement details from our Surat studio.</p><Link href={group.items[0][1]} className="text-link">Explore {group.label}</Link></div><div className="mega-links">{group.items.map(([label, href]) => <Link key={label} href={href}>{label}</Link>)}</div><div className="mega-image"><Image src={group.promo} alt="Tishla fashion" fill sizes="360px"/></div></div></div>}</div>)}
            <Link href="/lookbook" className="nav-item">LOOKBOOK</Link><Link href="/wholesale" className="nav-item">WHOLESALE</Link><Link href="/collections/sale" className="nav-item sale">SALE</Link>
          </div>
        </nav>
      </header>
      {children}
      <footer className="site-footer"><div className="container footer-grid"><div><Link href="/" className="footer-brand">TISHLA</Link><p>Contemporary Indian fashion from Surat. Made for the moments that stay with you.</p><div className="socials"><a href="https://instagram.com/tishlawear"><Camera/></a><a href="https://youtube.com/@tishlawear"><Play/></a><a href="https://facebook.com/tishlawear"><Globe2/></a><a href="https://wa.me/919574716712"><MessageCircle/></a></div></div><div><h4>Shop</h4><Link href="/shop">All Products</Link><Link href="/departments/sarees">Sarees</Link><Link href="/departments/lehengas">Lehengas</Link><Link href="/departments/kurtis-sets">Kurtis & Sets</Link><Link href="/departments/gowns">Gowns</Link></div><div><h4>Discover</h4><Link href="/collections/new-arrivals">New Arrivals</Link><Link href="/lookbook">Lookbook</Link><Link href="/about">Our Story</Link><Link href="/wholesale">Wholesale</Link><Link href="/contact">Contact</Link></div><div><h4>Customer Care</h4><Link href="/track-order">Track Order</Link><Link href="/shipping">Shipping & Delivery</Link><Link href="/returns">Returns</Link><Link href="/faq">FAQ</Link><Link href="/size-guide">Size Guide</Link></div></div><div className="footer-bottom container"><span>© 2026 Tishla by Purnika Sales. All rights reserved.</span><span>Engineered by Shakti Develops</span></div></footer>
      {mobile && <div className="mobile-drawer"><button className="icon-btn drawer-close" onClick={() => setMobile(false)}><X/></button><Image src="/branding/logo.png" alt="Tishla" width={84} height={84}/><div className="mobile-links"><Link href="/" onClick={()=>setMobile(false)}>Home</Link><Link href="/shop" onClick={()=>setMobile(false)}>Shop</Link><Link href="/collections/new-arrivals" onClick={()=>setMobile(false)}>New Arrivals</Link><Link href="/collections/wedding-edit" onClick={()=>setMobile(false)}>Wedding Edit</Link><Link href="/lookbook" onClick={()=>setMobile(false)}>Lookbook</Link><Link href="/wholesale" onClick={()=>setMobile(false)}>Wholesale</Link><Link href="/about" onClick={()=>setMobile(false)}>About Tishla</Link><Link href="/contact" onClick={()=>setMobile(false)}>Contact</Link></div></div>}
    </>
  );
}

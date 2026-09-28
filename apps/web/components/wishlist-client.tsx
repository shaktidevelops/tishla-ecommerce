"use client";
import Link from "next/link";
import Image from "next/image";
import { Heart } from "lucide-react";
import { useEffect, useState } from "react";
import { products, Product } from "@/lib/data";
export function WishlistClient(){const [items,setItems]=useState<Product[]>([]);useEffect(()=>{try{const slugs=JSON.parse(localStorage.getItem('tishla-wishlist')||'[]');setItems(products.filter(p=>slugs.includes(p.slug)))}catch{}},[]);if(!items.length)return <div className="empty-state"><Heart size={28} style={{margin:'0 auto 15px'}}/><span className="eyebrow">YOUR EDIT</span><h1>Wishlist</h1><p>Save pieces you love from any product page to build your private edit.</p><Link href="/shop" className="button button-dark">Explore products</Link></div>;return <div className="product-grid">{items.map(p=><Link key={p.slug} href={`/products/${p.slug}`} className="wish-card"><div><Image src={p.image} alt={p.name} fill sizes="25vw"/></div><h3>{p.name}</h3><span>₹{p.price.toLocaleString('en-IN')}</span></Link>)}</div>}

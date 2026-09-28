"use client";
import { createContext, useContext, useEffect, useMemo, useState } from "react";
import type { Product } from "@/lib/data";
type CartItem={product:Product;quantity:number};
type CartContext={items:CartItem[];count:number;subtotal:number;add:(p:Product)=>void;remove:(slug:string)=>void;change:(slug:string,q:number)=>void;clear:()=>void};
const Ctx=createContext<CartContext|undefined>(undefined);
export function CartProvider({children}:{children:React.ReactNode}){const [items,setItems]=useState<CartItem[]>([]);useEffect(()=>{try{const raw=localStorage.getItem('tishla-cart');if(raw)setItems(JSON.parse(raw))}catch{}},[]);useEffect(()=>{try{localStorage.setItem('tishla-cart',JSON.stringify(items))}catch{}},[items]);const add=(product:Product)=>setItems(xs=>{const found=xs.find(x=>x.product.slug===product.slug);return found?xs.map(x=>x.product.slug===product.slug?{...x,quantity:x.quantity+1}:x):[...xs,{product,quantity:1}]});const remove=(slug:string)=>setItems(xs=>xs.filter(x=>x.product.slug!==slug));const change=(slug:string,q:number)=>q<=0?remove(slug):setItems(xs=>xs.map(x=>x.product.slug===slug?{...x,quantity:q}:x));const clear=()=>setItems([]);const value=useMemo(()=>({items,count:items.reduce((s,x)=>s+x.quantity,0),subtotal:items.reduce((s,x)=>s+x.product.price*x.quantity,0),add,remove,change,clear}),[items]);return <Ctx.Provider value={value}>{children}</Ctx.Provider>}
export function useCart(){const v=useContext(Ctx);if(!v)throw new Error('useCart must be used inside CartProvider');return v}

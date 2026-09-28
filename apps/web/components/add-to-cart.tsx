"use client";
import { Check, ShoppingBag } from "lucide-react";
import { useState } from "react";
import { useCart } from "./cart-provider";
import type { Product } from "@/lib/data";
export function AddToCart({product}:{product:Product}){const {add}=useCart();const [done,setDone]=useState(false);return <button className="button button-dark grow" onClick={()=>{add(product);setDone(true);setTimeout(()=>setDone(false),1400)}}>{done?<><Check size={16}/> Added to bag</>:<><ShoppingBag size={16}/> Add to bag</>}</button>}

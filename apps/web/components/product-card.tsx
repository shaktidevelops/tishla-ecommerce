import Image from "next/image";
import Link from "next/link";
import { Heart, Eye, ArrowRight } from "lucide-react";
import { AddToCart } from "./add-to-cart";
import type { Product } from "@/lib/data";

export function ProductCard({ product }: { product: Product }) {
  const swatch = product.color === "Wine" ? "#5a1025" : product.color === "Emerald" ? "#0b6b54" : product.color === "Gold" ? "#c9a227" : product.color === "Rose" ? "#b96b7d" : product.color === "Midnight" ? "#202538" : "#d8c4a6";
  return <article className="product-card">
    <div className="product-media">
      <Link href={`/products/${product.slug}`} aria-label={`View ${product.name}`}>
        <Image src={product.image} alt={product.name} fill sizes="(max-width: 620px) 50vw, (max-width: 1100px) 33vw, 25vw"/>
        <Image src={product.hoverImage} alt="" fill sizes="(max-width: 620px) 50vw, (max-width: 1100px) 33vw, 25vw" className="hover-image"/>
      </Link>
      <div className="product-badges"><span>{product.badge || "Tishla Edit"}</span></div>
      <button className="wishlist-float" aria-label={`Add ${product.name} to wishlist`}><Heart size={17}/></button>
      <Link href={`/products/${product.slug}`} className="quick-view"><Eye size={14}/> Quick view</Link>
    </div>
    <div className="product-info">
      <div className="product-meta"><span>{product.department}</span><span>{product.color}</span></div>
      <h3><Link href={`/products/${product.slug}`}>{product.name}</Link></h3>
      <div className="rating"><span>★★★★★</span><small>{product.rating} · {product.reviews} reviews</small></div>
      <div className="price"><strong>₹{product.price.toLocaleString("en-IN")}</strong>{product.compareAtPrice && <del>₹{product.compareAtPrice.toLocaleString("en-IN")}</del>}</div>
      <div className="swatches"><i style={{background: swatch}}/><i style={{background:"#f3eee5"}}/><span>Colour options</span><ArrowRight size={12}/></div>
      <div className="product-card-actions"><AddToCart product={product}/><Link className="card-details-link" href={`/products/${product.slug}`}>Details <ArrowRight size={13}/></Link></div>
    </div>
  </article>;
}

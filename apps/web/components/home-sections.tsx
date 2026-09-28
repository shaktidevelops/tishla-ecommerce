import Image from "next/image";
import Link from "next/link";
import { ProductCard } from "./product-card";
import { SectionTitle } from "./section-title";
import { departments, products } from "@/lib/data";

export function HomeSections() {
  const featured = products.slice(0,4);
  const departmentsImages = [
    ["Sarees","/departments/sarees","https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=1000&q=85"],
    ["Lehengas","/departments/lehengas","https://images.unsplash.com/photo-1610189012906-5e132fbbc77f?auto=format&fit=crop&w=1000&q=85"],
    ["Kurtis & Sets","/departments/kurtis-sets","https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=1000&q=85"],
    ["Gowns","/departments/gowns","https://images.unsplash.com/photo-1539008835657-9e8e9680c956?auto=format&fit=crop&w=1000&q=85"],
  ];
  return <main><section className="container section-pad"><SectionTitle kicker="SHOP THE DEPARTMENTS" title="Designed for every kind of occasion"/><div className="department-grid">{departmentsImages.map(([name,href,src])=><Link href={href} key={name} className="department-card"><Image src={src} alt={name} fill sizes="(max-width: 900px) 50vw, 25vw"/><div><span>{name}</span><b>Shop now →</b></div></Link>)}</div></section>
  <section className="container section-pad"><SectionTitle kicker="NEW ARRIVALS" title="A fresh take on Indian dressing" href="/collections/new-arrivals"/><div className="product-grid">{featured.map(p=><ProductCard key={p.slug} product={p}/>)}</div></section>
  <section className="editorial"><div className="container editorial-grid"><div className="editorial-image"><Image src="https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1400&q=88" alt="Saree editorial" fill sizes="50vw"/></div><div className="editorial-copy"><span className="eyebrow">THE WEDDING EDIT</span><h2>For vows, festivities & everything between.</h2><p>Rich silk, intricate zari, and modern silhouettes come together for a collection built around the biggest moments of the year.</p><Link href="/collections/wedding-edit" className="button button-dark">Explore the Wedding Edit</Link></div></div></section>
  <section className="container section-pad"><SectionTitle kicker="SHOP BY OCCASION" title="Find the look by the moment"/><div className="occasion-grid">{[["Wedding","/collections/wedding-edit"],["Festive","/collections/festive-edit"],["Party","/collections/party-edit"],["Office","/shop?style=office"]].map(([label,href],i)=><Link href={href} key={label} className={`occasion-card o${i+1}`}><div><span>0{i+1}</span><h3>{label}</h3><b>Explore →</b></div></Link>)}</div></section>
  <section className="container section-pad"><div className="split-band"><div><span className="eyebrow">SURAT • INDIA</span><h2>Made close to the craft.</h2><p>Our collections are developed in Surat with a focus on fabric, finishing and the details that make a garment feel special.</p><Link href="/about" className="text-link">Our story →</Link></div><div className="texture-panel"><div className="texture-orb"/><span>TISHLA<br/>CRAFT</span></div></div></section>
  <section className="newsletter"><div className="container newsletter-inner"><div><span className="eyebrow light">JOIN THE EDIT</span><h2>First access to new drops.</h2><p>New collections, editorial stories and private offers. In your inbox.</p></div><form><input type="email" placeholder="Your email address"/><button className="button button-light">Subscribe</button></form></div></section></main>;
}

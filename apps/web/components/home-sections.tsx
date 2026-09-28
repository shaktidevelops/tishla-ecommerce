import Image from "next/image";
import Link from "next/link";
import { ArrowRight, ChevronRight, Sparkles, Truck, ShieldCheck, RotateCcw, Gem } from "lucide-react";
import { ProductCard } from "./product-card";
import { products } from "@/lib/data";

const departments = [
  ["Sarees", "/departments/sarees", "https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=900&q=88"],
  ["Lehengas", "/departments/lehengas", "https://images.unsplash.com/photo-1610189012906-5e132fbbc77f?auto=format&fit=crop&w=900&q=88"],
  ["Kurtis & Sets", "/departments/kurtis-sets", "https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=900&q=88"],
  ["Gowns", "/departments/gowns", "https://images.unsplash.com/photo-1539008835657-9e8e9680c956?auto=format&fit=crop&w=900&q=88"],
  ["Suits", "/departments/suits", "https://images.unsplash.com/photo-1591369822096-ffd140ec948f?auto=format&fit=crop&w=900&q=88"],
  ["Dupattas", "/departments/dupattas", "https://images.unsplash.com/photo-1601924928376-5b3e7b9b3a5c?auto=format&fit=crop&w=900&q=88"],
  ["Blouses", "/departments/blouses", "https://images.unsplash.com/photo-1595341888016-a392ef81b7de?auto=format&fit=crop&w=900&q=88"],
  ["Accessories", "/departments/accessories", "https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=900&q=88"],
];

const campaigns = [
  ["Wedding Edit", "For the moments you dress up for.", "/collections/wedding-edit", "https://images.unsplash.com/photo-1583391733956-6c78276477e2?auto=format&fit=crop&w=1400&q=90"],
  ["Festive Edit", "Colour, craft and celebration.", "/collections/festive-edit", "https://images.unsplash.com/photo-1621784563330-caee0b138a00?auto=format&fit=crop&w=1400&q=90"],
  ["Party Edit", "After-dark glamour, made modern.", "/collections/party-edit", "https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=1400&q=90"],
  ["New Arrivals", "Fresh styles, just in.", "/collections/new-arrivals", "https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=1400&q=90"],
];

const occasions = [
  ["Weddings", "/collections/wedding-edit", "https://images.unsplash.com/photo-1604014237800-1c9102c219da?auto=format&fit=crop&w=1200&q=88"],
  ["Festivals", "/collections/festive-edit", "https://images.unsplash.com/photo-1609743522653-52354461eb27?auto=format&fit=crop&w=1200&q=88"],
  ["Party Nights", "/collections/party-edit", "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1200&q=88"],
  ["Everyday", "/shop?style=casual", "https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=1200&q=88"],
];

const benefits = [
  [Truck, "Fast dispatch", "From our Surat studio"],
  [Gem, "Premium quality", "Curated fabrics & finish"],
  [ShieldCheck, "Secure checkout", "Protected payments"],
  [RotateCcw, "Easy returns", "Simple, clear policies"],
];

export function HomeSections() {
  const featured = products.slice(0, 8);
  return (
    <main>
      <section className="benefit-bar">
        <div className="container benefit-grid">
          {benefits.map(([Icon, title, body]) => {
            const I = Icon as typeof Truck;
            return <div className="benefit-item" key={String(title)}><I/><span><strong>{String(title)}</strong><small>{String(body)}</small></span></div>;
          })}
        </div>
      </section>

      <section className="container section-pad section-pad-tight">
        <div className="section-heading-row"><div><span className="eyebrow">EXPLORE TISHLA</span><h2>Shop by department</h2><p>Curated categories for every expression of modern Indian dressing.</p></div><Link href="/shop" className="text-link">View all <ArrowRight size={16}/></Link></div>
        <div className="department-rail">
          {departments.map(([name, href, src]) => <Link href={href} key={name} className="department-tile"><div className="department-image"><Image src={src} alt={name} fill sizes="150px"/></div><span>{name}</span><small>Shop <ChevronRight size={13}/></small></Link>)}
        </div>
      </section>

      <section className="container section-pad">
        <div className="section-heading-row"><div><span className="eyebrow">THE TISHLA EDIT</span><h2>Featured collections</h2><p>Distinct moods, refined into effortless occasion dressing.</p></div><Link href="/shop" className="text-link">Explore all <ArrowRight size={16}/></Link></div>
        <div className="campaign-grid">
          {campaigns.map(([title, copy, href, src], i) => <Link href={href} key={title} className={`campaign-card campaign-${i+1}`}><Image src={src} alt={title} fill sizes="(max-width: 800px) 100vw, 50vw"/><div className="campaign-overlay"/><div className="campaign-copy"><span>0{i+1}</span><h3>{title}</h3><p>{copy}</p><b>Shop collection <ArrowRight size={15}/></b></div></Link>)}
        </div>
      </section>

      <section className="container section-pad">
        <div className="section-heading-row"><div><span className="eyebrow">NEW SEASON</span><h2>New arrivals</h2><p>Fresh silhouettes, colour stories and textures to know now.</p></div><Link href="/collections/new-arrivals" className="text-link">Shop new in <ArrowRight size={16}/></Link></div>
        <div className="product-grid product-grid-4">{featured.map(p => <ProductCard key={p.slug} product={p}/>)}</div>
      </section>

      <section className="editorial-full">
        <div className="editorial-full-image"><Image src="https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1800&q=92" alt="Tishla wedding edit" fill sizes="100vw"/></div>
        <div className="editorial-full-shade"/>
        <div className="container editorial-full-copy"><span className="eyebrow light">THE WEDDING EDIT</span><h2>For vows, festivities<br/><em>&amp; everything between.</em></h2><p>Rich silk, intricate zari and modern silhouettes, composed for the season's biggest moments.</p><Link href="/collections/wedding-edit" className="button button-light">Explore the edit <ArrowRight size={16}/></Link></div>
      </section>

      <section className="container section-pad">
        <div className="section-heading-row"><div><span className="eyebrow">SHOP BY OCCASION</span><h2>Find the look by the moment</h2><p>From the first invite to the last dance.</p></div></div>
        <div className="occasion-grid occasion-grid-rich">{occasions.map(([name, href, src], i) => <Link href={href} key={name} className="occasion-card-rich"><Image src={src} alt={name} fill sizes="(max-width: 800px) 50vw, 25vw"/><div className="occasion-shade"/><div className="occasion-copy"><small>0{i+1}</small><h3>{name}</h3><span>Explore <ArrowRight size={15}/></span></div></Link>)}</div>
      </section>

      <section className="craft-section">
        <div className="container craft-grid">
          <div className="craft-copy"><span className="eyebrow">SURAT • INDIA</span><h2>Made close to the craft.</h2><p>Tishla brings together the energy of Surat's textile world with a quieter, editorial approach to Indian fashion—considered fabrics, confident colour and details that reward a closer look.</p><div className="craft-points"><span><Sparkles size={16}/>Curated collections</span><span><Gem size={16}/>Fabric-first quality</span><span><Truck size={16}/>Direct from Surat</span></div><Link href="/about" className="button button-dark">Discover Tishla <ArrowRight size={16}/></Link></div>
          <div className="craft-visual"><Image src="/branding/banner.png" alt="Tishla by Purnika Sales" fill sizes="(max-width: 900px) 100vw, 50vw"/><div className="craft-frame"><span>TISHLA</span><small>BY PURNIKA SALES</small></div></div>
        </div>
      </section>

      <section className="container section-pad social-section">
        <div className="section-heading-row"><div><span className="eyebrow">@TISHLAWEAR</span><h2>See how the edit comes alive.</h2><p>Campaigns, new drops and styling ideas from Tishla.</p></div><a href="https://instagram.com/tishlawear" className="text-link">Follow on Instagram <ArrowRight size={16}/></a></div>
        <div className="social-grid">{[
          "https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=800&q=86",
          "https://images.unsplash.com/photo-1610189012906-5e132fbbc77f?auto=format&fit=crop&w=800&q=86",
          "https://images.unsplash.com/photo-1583391733956-6c78276477e2?auto=format&fit=crop&w=800&q=86",
          "https://images.unsplash.com/photo-1621784563330-caee0b138a00?auto=format&fit=crop&w=800&q=86",
          "/branding/banner.png",
          "https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=800&q=86",
        ].map((src, i) => <a href="https://instagram.com/tishlawear" key={src+i} className="social-card"><Image src={src} alt="Tishla on Instagram" fill sizes="(max-width: 700px) 50vw, 16vw"/></a>)}</div>
      </section>

      <section className="newsletter"><div className="container newsletter-inner"><div><span className="eyebrow light">JOIN THE TISHLA EDIT</span><h2>First access to new drops.</h2><p>New collections, editorial stories and private offers, delivered selectively.</p></div><form><input type="email" aria-label="Email address" placeholder="Your email address"/><button className="button button-light">Subscribe</button></form></div></section>
    </main>
  );
}

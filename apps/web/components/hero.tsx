import Image from "next/image";
import Link from "next/link";

export function Hero() {
  return <section className="hero"><Image src="/branding/banner.png" alt="Tishla campaign" fill priority sizes="100vw" className="hero-image"/><div className="hero-overlay"/><div className="container hero-content"><span className="eyebrow light">THE TISHLA EDIT • 2026</span><h1>Indian craft.<br/><em>Contemporary spirit.</em></h1><p>Statement drapes, occasion dressing and everyday elegance, curated from Surat.</p><div className="hero-actions"><Link href="/collections/new-arrivals" className="button button-light">Shop New Arrivals</Link><Link href="/departments/sarees" className="button button-ghost-light">Explore Sarees</Link></div></div></section>;
}

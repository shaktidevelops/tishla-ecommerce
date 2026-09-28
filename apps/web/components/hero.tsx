import Image from "next/image";
import Link from "next/link";
import { ArrowRight, ChevronLeft, ChevronRight } from "lucide-react";

export function Hero() {
  return (
    <section className="hero-v3">
      <Image src="/branding/banner.png" alt="Tishla by Purnika Sales" fill priority sizes="100vw" className="hero-v3-image"/>
      <div className="hero-v3-shade"/>
      <div className="hero-v3-content container">
        <div className="hero-v3-copy">
          <span className="eyebrow light">TISHLA • NEW SEASON</span>
          <h1>Timeless Indian elegance.<br/><em>Reframed for now.</em></h1>
          <p>Statement sarees, occasion dressing and modern Indian essentials, curated from Surat.</p>
          <div className="hero-actions"><Link href="/new-in" className="button button-light">Shop new arrivals <ArrowRight size={16}/></Link><Link href="/collections/wedding-edit" className="button button-ghost-light">Wedding edit</Link></div>
        </div>
        <div className="hero-v3-sidecard"><span>THE WEDDING EDIT</span><strong>Dress for<br/>the moment.</strong><Link href="/collections/wedding-edit">Explore collection <ArrowRight size={15}/></Link></div>
      </div>
      <div className="hero-v3-controls"><button aria-label="Previous slide"><ChevronLeft size={18}/></button><span>01 / 04</span><button aria-label="Next slide"><ChevronRight size={18}/></button></div>
    </section>
  );
}

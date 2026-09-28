import { ProductCard } from "@/components/product-card";
import { products } from "@/lib/data";
export default function ShopPage(){return <main className="container page-pad"><div className="page-hero"><span className="eyebrow">SHOP ALL</span><h1>Everything Tishla</h1><p>Explore the full edit across sarees, occasion wear and everyday Indian fashion.</p></div><div className="toolbar"><span>{products.length} products</span><div><button>Filter</button><button>Sort: Featured</button></div></div><div className="product-grid">{products.map(p=><ProductCard product={p} key={p.slug}/>)}</div></main>}

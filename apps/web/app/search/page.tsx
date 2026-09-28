import { ProductCard } from "@/components/product-card";
import { products } from "@/lib/data";
export default function SearchPage(){return <main className="container page-pad"><div className="page-hero"><span className="eyebrow">SEARCH</span><h1>Find your next favourite.</h1><div className="search-large"><input placeholder="Search sarees, fabrics, occasions..."/><button className="button button-dark">Search</button></div></div><div className="product-grid">{products.slice(0,4).map(p=><ProductCard product={p} key={p.slug}/>)}</div></main>}

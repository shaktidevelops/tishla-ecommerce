import { notFound } from "next/navigation";
import { ProductGridClient } from "@/components/product-grid-client";
import { departments, products } from "@/lib/data";
function normalize(v:string){return v.toLowerCase().replaceAll('&','and').replaceAll(/[^a-z0-9]+/g,'-').replaceAll(/(^-|-$)/g,'');}
export default async function DepartmentPage({params}:{params:Promise<{slug:string}>}){const {slug}=await params;const d=departments.find(x=>x.slug===slug);if(!d)notFound();const items=products.filter(p=>normalize(p.department)===slug);return <main className="container page-pad"><div className="page-hero"><span className="eyebrow">DEPARTMENT</span><h1>{d.name}</h1><p>{d.description}</p></div><div className="category-nav"><span>Shop this edit</span><a href="/new-in">New</a><a href="/collections/bestsellers">Bestsellers</a><a href="/collections/festive-edit">Festive</a><a href="/collections/wedding-edit">Occasion</a></div><ProductGridClient products={items.length ? items : products}/></main>}

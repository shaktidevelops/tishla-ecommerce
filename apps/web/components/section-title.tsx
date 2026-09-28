import Link from "next/link";
export function SectionTitle({ kicker, title, href, linkText="View all" }: { kicker: string; title: string; href?: string; linkText?: string }) { return <div className="section-title"><div><span className="eyebrow">{kicker}</span><h2>{title}</h2></div>{href && <Link href={href} className="text-link">{linkText} →</Link>}</div>; }

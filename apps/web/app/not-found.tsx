import Link from 'next/link';
export default function NotFound(){return <main className="container page-pad"><div className="empty-state"><span className="eyebrow">404</span><h1>This piece has moved.</h1><p>The page you're looking for isn't part of the current Tishla edit.</p><Link href="/" className="button button-dark">Return home</Link></div></main>}

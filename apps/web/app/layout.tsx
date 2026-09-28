import type { Metadata } from "next";
import "../globals.css";
import { SiteChrome } from "@/components/site-chrome";
import { CartProvider } from "@/components/cart-provider";

export const metadata: Metadata = {
  title: "Tishla by Purnika Sales | Luxury Indian Fashion",
  description: "Sarees, lehengas, kurtis, gowns and occasion wear from Tishla by Purnika Sales, Surat.",
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000"),
  icons: { icon: "/branding/favicon.png" },
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return <html lang="en"><body><CartProvider><SiteChrome>{children}</SiteChrome></CartProvider></body></html>;
}

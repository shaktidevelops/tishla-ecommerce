import { products } from "./data";

const API = process.env.NEXT_PUBLIC_API_BASE_URL;

export async function fetchProducts(): Promise<typeof products> {
  if (!API) return products;
  try {
    const response = await fetch(`${API}/api/storefront/products`, { next: { revalidate: 30 } });
    if (!response.ok) return products;
    const data = await response.json();
    return Array.isArray(data) && data.length ? data : products;
  } catch {
    return products;
  }
}

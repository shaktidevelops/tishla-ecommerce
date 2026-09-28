import { products } from "./data";

const API = process.env.BACKEND_URL || process.env.NEXT_PUBLIC_API_BASE_URL || "http://127.0.0.1:8000";
const endpoint = API.endsWith("/api") ? API : `${API}/api`;

export async function fetchProducts(): Promise<typeof products> {
  try {
    const response = await fetch(`${endpoint}/storefront/products`, { next: { revalidate: 30 } });
    if (!response.ok) return products;
    const data = await response.json();
    return Array.isArray(data) && data.length ? data : products;
  } catch {
    return products;
  }
}

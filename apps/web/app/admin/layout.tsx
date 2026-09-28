import "./admin.css";
import { AdminNav } from "./components/admin-nav";
import { AdminProvider } from "./components/admin-provider";

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return <AdminProvider><div className="admin-shell"><AdminNav/><section className="admin-content">{children}</section></div></AdminProvider>;
}

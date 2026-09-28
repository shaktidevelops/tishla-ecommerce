import { AdminProvider } from "./components/admin-provider";
import { AdminShell } from "./components/admin-shell";

export default function AdminLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return <AdminProvider><AdminShell>{children}</AdminShell></AdminProvider>;
}

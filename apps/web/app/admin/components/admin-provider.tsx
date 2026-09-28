"use client";

import { createContext, useContext, useEffect, useMemo, useState } from "react";
import { LockKeyhole, LogIn, LoaderCircle, Store } from "lucide-react";

const API = process.env.NEXT_PUBLIC_API_BASE_URL || "http://127.0.0.1:8000";

type AdminUser = { id: string; email: string; full_name: string; roles: string[] };

type AdminContextValue = {
  token: string;
  user: AdminUser | null;
  ready: boolean;
  api: <T = any>(path: string, options?: RequestInit) => Promise<T>;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
};

function errorMessage(value: unknown, fallback = "Request failed."): string {
  if (value instanceof Error) return value.message;
  if (typeof value === "string" && value.trim()) return value;
  if (Array.isArray(value)) {
    return value
      .map((item) => {
        if (typeof item === "string") return item;
        if (item && typeof item === "object") {
          const obj = item as Record<string, unknown>;
          const loc = Array.isArray(obj.loc) ? obj.loc.join(" → ") : "";
          const msg = typeof obj.msg === "string" ? obj.msg : "";
          return loc && msg ? `${loc}: ${msg}` : msg || JSON.stringify(item);
        }
        return String(item);
      })
      .filter(Boolean)
      .join(" • ") || fallback;
  }
  if (value && typeof value === "object") {
    const obj = value as Record<string, unknown>;
    if (typeof obj.message === "string") return obj.message;
    if (typeof obj.detail === "string") return obj.detail;
    if (typeof obj.error === "string") return obj.error;
    try { return JSON.stringify(value); } catch { return fallback; }
  }
  return fallback;
}

const AdminContext = createContext<AdminContextValue | null>(null);

export function useAdmin() {
  const value = useContext(AdminContext);
  if (!value) throw new Error("useAdmin must be used inside AdminProvider");
  return value;
}

export function AdminProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useState("");
  const [user, setUser] = useState<AdminUser | null>(null);
  const [ready, setReady] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    const savedToken = window.localStorage.getItem("tishla_admin_access") || "";
    const savedUser = window.localStorage.getItem("tishla_admin_user");
    setToken(savedToken);
    if (savedUser) {
      try { setUser(JSON.parse(savedUser)); } catch { window.localStorage.removeItem("tishla_admin_user"); }
    }
    setReady(true);
  }, []);

  const api = useMemo(() => async <T,>(path: string, options: RequestInit = {}): Promise<T> => {
    const headers = new Headers(options.headers || {});
    if (token) headers.set("Authorization", `Bearer ${token}`);
    if (options.body && !(options.body instanceof FormData)) headers.set("Content-Type", "application/json");
    const response = await fetch(`${API}${path}`, { ...options, headers, cache: "no-store" });
    const data = await response.json().catch(() => ({}));
    if (response.status === 401) {
      window.localStorage.removeItem("tishla_admin_access");
      window.localStorage.removeItem("tishla_admin_user");
      setToken("");
      setUser(null);
    }
    if (!response.ok) throw new Error(errorMessage(data.detail ?? data.error));
    return data as T;
  }, [token]);

  async function login(nextEmail: string, nextPassword: string) {
    setBusy(true);
    setError("");
    try {
      const response = await fetch(`${API}/api/auth/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: nextEmail.trim(), password: nextPassword }),
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(errorMessage(data.detail ?? data.error, "Invalid email or password."));
      setToken(data.access_token);
      setUser(data.user);
      window.localStorage.setItem("tishla_admin_access", data.access_token);
      window.localStorage.setItem("tishla_admin_user", JSON.stringify(data.user));
    } catch (err) {
      setError(err instanceof Error ? err.message : "Login failed.");
      throw err;
    } finally {
      setBusy(false);
    }
  }

  function logout() {
    setToken("");
    setUser(null);
    window.localStorage.removeItem("tishla_admin_access");
    window.localStorage.removeItem("tishla_admin_user");
  }

  if (!ready) return <div className="admin-loading"><LoaderCircle className="spin"/><span>Loading Tishla Control Room…</span></div>;

  if (!token || !user) {
    return (
      <div className="admin-login-screen">
        <div className="admin-login-card">
          <div className="admin-login-brand"><Store size={20}/><span>TISHLA CONTROL ROOM</span></div>
          <div className="admin-login-mark"><LockKeyhole size={24}/></div>
          <p className="eyebrow">SECURE ADMINISTRATION</p>
          <h1>Welcome back.</h1>
          <p className="admin-login-copy">Manage catalogue, orders, merchandising and store settings from one place.</p>
          <label>Email<input value={email} onChange={(e) => setEmail(e.target.value)} type="email" placeholder="admin@tishla.com" autoComplete="username"/></label>
          <label>Password<input value={password} onChange={(e) => setPassword(e.target.value)} type="password" placeholder="••••••••" autoComplete="current-password" onKeyDown={(e) => { if (e.key === "Enter") login(email, password).catch(() => {}); }}/></label>
          {error && <div className="admin-error">{error}</div>}
          <button className="button button-dark admin-login-btn" disabled={busy} onClick={() => login(email, password).catch(() => {})}>
            {busy ? <LoaderCircle className="spin" size={16}/> : <LogIn size={16}/>} {busy ? "Signing in…" : "Sign in"}
          </button>
          <div className="admin-login-foot">Tishla by Purnika Sales · Surat</div>
        </div>
      </div>
    );
  }

  return <AdminContext.Provider value={{ token, user, ready, api, login, logout }}>{children}</AdminContext.Provider>;
}

"use client";

import { createContext, useContext, useEffect, useMemo, useState } from "react";
import { LockKeyhole, LogIn, LoaderCircle, Store } from "lucide-react";

type AdminUser = { id: string; email: string; full_name: string; roles: string[] };

type AdminContextValue = {
  token: string;
  user: AdminUser | null;
  ready: boolean;
  api: <T = any>(path: string, options?: RequestInit) => Promise<T>;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
};

const AdminContext = createContext<AdminContextValue | null>(null);

export function useAdmin() {
  const value = useContext(AdminContext);
  if (!value) throw new Error("useAdmin must be used inside AdminProvider");
  return value;
}

function getErrorMessage(data: unknown, fallback: string): string {
  if (!data || typeof data !== "object") return fallback;
  const record = data as { detail?: unknown; error?: unknown };

  if (typeof record.detail === "string") return record.detail;
  if (typeof record.error === "string") return record.error;

  if (Array.isArray(record.detail)) {
    return record.detail
      .map((item) => {
        if (!item || typeof item !== "object") return String(item);
        const message = (item as { msg?: unknown }).msg;
        return typeof message === "string" ? message : JSON.stringify(item);
      })
      .join(" • ");
  }

  return fallback;
}

async function parseResponse(response: Response) {
  const contentType = response.headers.get("content-type") || "";
  if (contentType.includes("application/json")) {
    return response.json().catch(() => ({}));
  }
  const text = await response.text().catch(() => "");
  return text ? { error: text } : {};
}

export function AdminProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useState("");
  const [user, setUser] = useState<AdminUser | null>(null);
  const [ready, setReady] = useState(false);
  const [email, setEmail] = useState("purnikasales@gmail.com");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    const savedToken = window.localStorage.getItem("tishla_admin_access") || "";
    const savedUser = window.localStorage.getItem("tishla_admin_user");

    setToken(savedToken);

    if (savedUser) {
      try {
        setUser(JSON.parse(savedUser));
      } catch {
        window.localStorage.removeItem("tishla_admin_user");
      }
    }

    setReady(true);
  }, []);

  const api = useMemo(
    () =>
      async <T,>(path: string, options: RequestInit = {}): Promise<T> => {
        const headers = new Headers(options.headers || {});

        if (token) headers.set("Authorization", `Bearer ${token}`);
        if (options.body && !(options.body instanceof FormData)) {
          headers.set("Content-Type", "application/json");
        }

        try {
          const response = await fetch(path, {
            ...options,
            headers,
            cache: "no-store",
          });

          const data = await parseResponse(response);

          if (response.status === 401) {
            window.localStorage.removeItem("tishla_admin_access");
            window.localStorage.removeItem("tishla_admin_user");
            setToken("");
            setUser(null);
          }

          if (!response.ok) {
            throw new Error(getErrorMessage(data, `Request failed (${response.status}).`));
          }

          return data as T;
        } catch (err) {
          if (err instanceof TypeError) {
            throw new Error(
              "Tishla API is not reachable. Start the Tishla API service, then refresh this page."
            );
          }
          throw err;
        }
      },
    [token]
  );

  async function login(nextEmail: string, nextPassword: string) {
    setBusy(true);
    setError("");

    try {
      const response = await fetch("/api/auth/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          email: nextEmail.trim(),
          password: nextPassword,
        }),
        cache: "no-store",
      });

      const data = await parseResponse(response);

      if (!response.ok) {
        throw new Error(getErrorMessage(data, "Invalid email or password."));
      }

      setToken(data.access_token);
      setUser(data.user);

      window.localStorage.setItem("tishla_admin_access", data.access_token);
      window.localStorage.setItem("tishla_admin_user", JSON.stringify(data.user));
    } catch (err) {
      const message =
        err instanceof TypeError
          ? "Tishla API is not reachable. Start the Tishla API service, then refresh this page."
          : err instanceof Error
            ? err.message
            : "Login failed.";

      setError(message);
      throw new Error(message);
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

  if (!ready) {
    return (
      <div className="admin-loading">
        <LoaderCircle className="spin" />
        <span>Loading Tishla Control Room…</span>
      </div>
    );
  }

  if (!token || !user) {
    return (
      <div className="admin-login-screen">
        <div className="admin-login-card">
          <div className="admin-login-brand">
            <Store size={20} />
            <span>TISHLA CONTROL ROOM</span>
          </div>

          <div className="admin-login-mark">
            <LockKeyhole size={24} />
          </div>

          <p className="eyebrow">SECURE ADMINISTRATION</p>
          <h1>Welcome back.</h1>
          <p className="admin-login-copy">
            Manage catalogue, orders, merchandising and store settings from one place.
          </p>

          <label>
            Email
            <input
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              type="email"
              placeholder="admin@tishla.com"
              autoComplete="username"
            />
          </label>

          <label>
            Password
            <input
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              type="password"
              placeholder="••••••••"
              autoComplete="current-password"
              onKeyDown={(e) => {
                if (e.key === "Enter") login(email, password).catch(() => {});
              }}
            />
          </label>

          {error && <div className="admin-error">{error}</div>}

          <button
            className="button button-dark admin-login-btn"
            disabled={busy}
            onClick={() => login(email, password).catch(() => {})}
          >
            {busy ? <LoaderCircle className="spin" size={16} /> : <LogIn size={16} />}
            {busy ? "Signing in…" : "Sign in"}
          </button>

          <div className="admin-login-foot">Tishla by Purnika Sales · Surat</div>
        </div>
      </div>
    );
  }

  return (
    <AdminContext.Provider value={{ token, user, ready, api, login, logout }}>
      {children}
    </AdminContext.Provider>
  );
}

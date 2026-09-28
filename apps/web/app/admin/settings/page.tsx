"use client";

import { Check, LoaderCircle, Save } from "lucide-react";
import { useEffect, useState } from "react";
import { useAdmin } from "../components/admin-provider";

type SettingRow = { key: string; value: Record<string, unknown> };

type FormState = {
  name: string; domain: string; currency: string; country: string; state: string; city: string; support_whatsapp: string; support_email: string;
  gst_rate: string; shipping_charge: string; minimum_order_value: string; guest_checkout: boolean; cod_enabled: boolean; online_payment_enabled: boolean;
};

const defaults: FormState = { name: "Tishla by Purnika Sales", domain: "https://www.tishla.com", currency: "INR", country: "IN", state: "Gujarat", city: "Surat", support_whatsapp: "919574716712", support_email: "purnikasales@gmail.com", gst_rate: "5", shipping_charge: "0", minimum_order_value: "0", guest_checkout: true, cod_enabled: true, online_payment_enabled: false };

export default function SettingsPage() {
  const { api } = useAdmin();
  const [form, setForm] = useState<FormState>(defaults);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  useEffect(() => {
    (async () => {
      try {
        const response = await api<{ data: SettingRow[] }>("/api/admin/settings");
        const map = Object.fromEntries((response.data || []).map(item => [item.key, item.value || {}])) as Record<string, Record<string, unknown>>;
        const store = map.store || {};
        const checkout = map.checkout || {};
        const tax = map.tax || {};
        setForm(current => ({
          ...current,
          name: String(store.name ?? current.name),
          domain: String(store.domain ?? current.domain),
          currency: String(store.currency ?? current.currency),
          country: String(store.country ?? current.country),
          state: String(store.state ?? current.state),
          city: String(store.city ?? current.city),
          support_whatsapp: String(store.support_whatsapp ?? current.support_whatsapp),
          support_email: String(store.support_email ?? current.support_email),
          minimum_order_value: String(checkout.minimum_order_value ?? current.minimum_order_value),
          guest_checkout: Boolean(checkout.guest_checkout ?? current.guest_checkout),
          cod_enabled: Boolean(checkout.cod_enabled ?? current.cod_enabled),
          online_payment_enabled: Boolean(checkout.online_payment_enabled ?? current.online_payment_enabled),
          gst_rate: String(tax.gst_rate ?? current.gst_rate),
          shipping_charge: String(tax.default_shipping_charge ?? current.shipping_charge),
        }));
      } catch (err) { setError(err instanceof Error ? err.message : "Unable to load settings."); }
      finally { setLoading(false); }
    })();
  }, [api]);

  function patch<K extends keyof FormState>(key: K, value: FormState[K]) { setForm(current => ({ ...current, [key]: value })); }

  async function save() {
    setSaving(true); setMessage(""); setError("");
    try {
      await api(`/api/admin/settings/store`, { method: "PUT", body: JSON.stringify({ value: { name: form.name, domain: form.domain, currency: form.currency, country: form.country, state: form.state, city: form.city, support_whatsapp: form.support_whatsapp, support_email: form.support_email } }) });
      await api(`/api/admin/settings/checkout`, { method: "PUT", body: JSON.stringify({ value: { guest_checkout: form.guest_checkout, cod_enabled: form.cod_enabled, online_payment_enabled: form.online_payment_enabled, minimum_order_value: Number(form.minimum_order_value || 0) } }) });
      await api(`/api/admin/settings/tax`, { method: "PUT", body: JSON.stringify({ value: { gst_rate: Number(form.gst_rate || 0), default_shipping_charge: Number(form.shipping_charge || 0) } }) });
      setMessage("Settings saved successfully.");
    } catch (err) { setError(err instanceof Error ? err.message : "Unable to save settings."); }
    finally { setSaving(false); }
  }

  if (loading) return <div className="admin-loading"><LoaderCircle className="spin"/><span>Loading store settings…</span></div>;

  return <main className="admin-page">
    <div className="admin-page-head"><div><span className="eyebrow">STORE CONFIGURATION</span><h1>Store settings</h1><p>Control public brand details, checkout behaviour and default tax and shipping values.</p></div><button className="button button-dark" onClick={save} disabled={saving}>{saving ? <LoaderCircle className="spin" size={15}/> : <Save size={15}/>} {saving ? "Saving…" : "Save changes"}</button></div>
    {message && <div className="admin-message success" style={{ marginBottom: 14 }}><Check size={14}/> {message}</div>}
    {error && <div className="admin-message error" style={{ marginBottom: 14 }}>{error}</div>}
    <section className="admin-panel" style={{ marginBottom: 18 }}><h2>Brand & contact</h2><p className="admin-panel-sub">Source-of-truth values for storefront contact details and transactional messaging.</p><div className="admin-form-grid">
      {([['name','Store name'],['domain','Public domain'],['support_email','Support email'],['support_whatsapp','WhatsApp number'],['currency','Currency'],['country','Country'],['state','State / region'],['city','City']] as const).map(([key,label]) => <div className="admin-field" key={key}><label>{label}</label><input value={form[key]} onChange={e => patch(key,e.target.value)} /></div>)}
    </div></section>
    <section className="admin-panel" style={{ marginBottom: 18 }}><h2>Checkout</h2><p className="admin-panel-sub">Turn payment options and guest ordering on or off without changing application code.</p><div className="admin-form-grid three">
      <div className="admin-field"><label>Minimum order value</label><input type="number" min="0" value={form.minimum_order_value} onChange={e=>patch('minimum_order_value',e.target.value)}/></div>
      <label className="admin-toggle"><input type="checkbox" checked={form.guest_checkout} onChange={e=>patch('guest_checkout',e.target.checked)}/><span><b>Guest checkout</b><small>Allow checkout without an account.</small></span></label>
      <label className="admin-toggle"><input type="checkbox" checked={form.cod_enabled} onChange={e=>patch('cod_enabled',e.target.checked)}/><span><b>Cash on delivery</b><small>Enable COD at checkout.</small></span></label>
      <label className="admin-toggle"><input type="checkbox" checked={form.online_payment_enabled} onChange={e=>patch('online_payment_enabled',e.target.checked)}/><span><b>Online payments</b><small>Enable gateway checkout.</small></span></label>
    </div></section>
    <section className="admin-panel"><h2>Tax & shipping defaults</h2><p className="admin-panel-sub">Defaults for new commerce records; product and order overrides remain possible.</p><div className="admin-form-grid three"><div className="admin-field"><label>Default GST rate %</label><input type="number" min="0" max="100" step="0.01" value={form.gst_rate} onChange={e=>patch('gst_rate',e.target.value)}/></div><div className="admin-field"><label>Default shipping charge</label><input type="number" min="0" step="0.01" value={form.shipping_charge} onChange={e=>patch('shipping_charge',e.target.value)}/></div></div></section>
  </main>;
}

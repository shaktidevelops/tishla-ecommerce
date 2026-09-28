"use client";

import { ArrowLeft, ChevronRight, LoaderCircle, RefreshCw, Search, Truck } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { useAdmin } from "../components/admin-provider";

type Order = {
  id: string; order_number: string; status: string; customer_name: string;
  customer_phone: string | null; customer_email: string | null;
  subtotal: number; discount_total: number; tax_total: number; shipping_total: number;
  grand_total: number; line_count: number; item_count: number; created_at: string; placed_at: string;
};
type Detail = {
  order: Order & { currency: string; shipping_address: Record<string, unknown>; notes: string | null; source: string; payment_method: string | null };
  items: Array<{ id: string; sku: string; product_name: string; variant_name: string | null; quantity: number; unit_price: number; tax_rate: number; tax_total: number; line_total: number }>;
  payments: Array<{ id: string; provider: string; amount: number; currency: string; status: string; method: string | null; paid_at: string | null }>;
  shipments: Array<{ id: string; provider: string | null; tracking_number: string | null; status: string; shipping_method: string | null }>;
  history: Array<{ id: number; from_status: string | null; to_status: string; note: string | null; created_at: string; changed_by: string }>;
};

const STATUSES = [
  ["all","All statuses"],["pending_payment","Pending payment"],["paid","Paid"],["confirmed","Confirmed"],
  ["processing","Processing"],["packed","Packed"],["shipped","Shipped"],["delivered","Delivered"],
  ["cancelled","Cancelled"],["refunded","Refunded"],["returned","Returned"],["payment_failed","Payment failed"],
] as const;

const NEXT: Record<string,string[]> = {
  pending_payment:["paid","payment_failed","cancelled"], paid:["confirmed","cancelled","refunded"],
  confirmed:["processing","cancelled"], processing:["packed","cancelled"], packed:["shipped"],
  shipped:["delivered","returned"], delivered:["returned"], payment_failed:["pending_payment","cancelled"],
  cancelled:["refunded"], returned:["refunded"], refunded:[]
};

function money(value: number, currency = "INR") {
  return new Intl.NumberFormat("en-IN", {style:"currency",currency,maximumFractionDigits:0}).format(Number(value || 0));
}
function date(value: string | null | undefined) {
  return value ? new Intl.DateTimeFormat("en-IN",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"}).format(new Date(value)) : "—";
}
function statusLabel(value: string) {
  return value.replaceAll("_"," ").replace(/\b\w/g,(c) => c.toUpperCase());
}
function addressLines(value: Record<string,unknown> | null | undefined) {
  return value ? Object.values(value).filter((v) => v !== null && v !== undefined && String(v).trim()).map(String) : ["Address not available"];
}

export default function OrdersPage() {
  const {api} = useAdmin();
  const [orders,setOrders] = useState<Order[]>([]);
  const [query,setQuery] = useState("");
  const [status,setStatus] = useState("all");
  const [loading,setLoading] = useState(true);
  const [detail,setDetail] = useState<Detail | null>(null);
  const [error,setError] = useState("");
  const [message,setMessage] = useState("");
  const [note,setNote] = useState("");
  const [busy,setBusy] = useState<string | null>(null);

  async function load() {
    setLoading(true); setError("");
    try {
      const params = new URLSearchParams();
      if (query.trim()) params.set("q",query.trim());
      if (status !== "all") params.set("status",status);
      const result = await api<{data:Order[]}>("/api/admin/orders?" + params.toString());
      setOrders(result.data || []);
    } catch (err) { setError(err instanceof Error ? err.message : "Unable to load orders."); }
    finally { setLoading(false); }
  }

  async function openOrder(id:string) {
    setError(""); setMessage("");
    try {
      const result = await api<{data:Detail}>("/api/admin/orders/" + id);
      setDetail(result.data);
    } catch (err) { setError(err instanceof Error ? err.message : "Unable to load order details."); }
  }

  async function updateStatus(next:string) {
    if (!detail) return;
    setBusy(next); setError(""); setMessage("");
    try {
      const result = await api<{data:{order_number:string;status:string}}>("/api/admin/orders/" + detail.order.id + "/status",{
        method:"PATCH",body:JSON.stringify({status:next,note:note.trim() || null})
      });
      setNote("");
      setMessage(result.data.order_number + " moved to " + statusLabel(result.data.status) + ".");
      await load();
      await openOrder(detail.order.id);
    } catch (err) { setError(err instanceof Error ? err.message : "Unable to update order status."); }
    finally { setBusy(null); }
  }

  useEffect(() => { load(); }, [status]);

  const totals = useMemo(() => ({
    count:orders.length,
    units:orders.reduce((n,o)=>n+Number(o.item_count||0),0),
    value:orders.reduce((n,o)=>n+Number(o.grand_total||0),0)
  }),[orders]);

  if (detail) {
    const order = detail.order;
    const nextStatuses = NEXT[order.status] || [];
    return <main className="admin-page">
      <div className="admin-page-head">
        <div>
          <button className="admin-back-link" onClick={()=>setDetail(null)}><ArrowLeft size={14}/> Back to orders</button>
          <span className="eyebrow">ORDER OPERATIONS</span><h1>{order.order_number}</h1>
          <p>{date(order.created_at)} · {order.customer_name} · {statusLabel(order.status)}</p>
        </div>
        <button className="button" onClick={()=>openOrder(order.id)}><RefreshCw size={14}/> Refresh</button>
      </div>
      {(message || error) && <div className={"admin-message " + (error ? "error":"success")} style={{marginBottom:14}}>{error || message}</div>}

      <div className="admin-order-grid">
        <section className="admin-panel">
          <div className="admin-panel-title-row"><div><h2>Order summary</h2><p className="admin-panel-sub">Customer, payment and totals.</p></div><span className="admin-status-dot">{statusLabel(order.status)}</span></div>
          <div className="admin-order-summary-grid">
            <div><span>Customer</span><strong>{order.customer_name}</strong><small>{order.customer_email || "No email"} · {order.customer_phone || "No phone"}</small></div>
            <div><span>Payment</span><strong>{order.payment_method || "—"}</strong><small>{order.source || "web"} order</small></div>
            <div><span>Subtotal</span><strong>{money(order.subtotal,order.currency)}</strong><small>Discount {money(order.discount_total,order.currency)}</small></div>
            <div><span>Grand total</span><strong>{money(order.grand_total,order.currency)}</strong><small>Tax {money(order.tax_total,order.currency)} · Shipping {money(order.shipping_total,order.currency)}</small></div>
          </div>
        </section>

        <section className="admin-panel">
          <div className="admin-panel-title-row"><div><h2>Update status</h2><p className="admin-panel-sub">Only valid transitions are offered.</p></div><Truck size={18} className="admin-panel-icon"/></div>
          {nextStatuses.length ? <><div className="admin-status-actions">
            {nextStatuses.map(next=><button key={next} className="admin-status-action" disabled={!!busy} onClick={()=>updateStatus(next)}>
              {busy===next ? <LoaderCircle size={14} className="spin"/> : <ChevronRight size={14}/>} {statusLabel(next)}
            </button>)}
          </div><div className="admin-field" style={{marginTop:12}}><label>Status note (optional)</label><textarea value={note} onChange={e=>setNote(e.target.value)} placeholder="Add an internal fulfilment note…"/></div></> : <div className="admin-message">No further transitions from this status.</div>}
        </section>

        <section className="admin-panel admin-order-wide">
          <h2>Items</h2><p className="admin-panel-sub">{detail.items.length} line item(s) · {order.item_count} unit(s).</p>
          <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>SKU</th><th>Product</th><th>Variant</th><th>Qty</th><th>Unit</th><th>Tax</th><th>Total</th></tr></thead>
          <tbody>{detail.items.map(item=><tr key={item.id}><td>{item.sku}</td><td><strong>{item.product_name}</strong></td><td>{item.variant_name || "—"}</td><td>{item.quantity}</td><td>{money(item.unit_price,order.currency)}</td><td>{money(item.tax_total,order.currency)} <span className="admin-table-muted">({item.tax_rate}%)</span></td><td><strong>{money(item.line_total,order.currency)}</strong></td></tr>)}</tbody></table></div>
        </section>

        <section className="admin-panel">
          <h2>Shipping address</h2><p className="admin-panel-sub">Captured at checkout.</p>
          <div className="admin-address">{addressLines(order.shipping_address).map((line,i)=><span key={line + i}>{line}</span>)}</div>
        </section>

        <section className="admin-panel">
          <h2>Payment & shipment</h2><p className="admin-panel-sub">Latest operational records.</p>
          <div className="admin-mini-list">
            {detail.payments.length ? detail.payments.map(p=><div className="admin-mini-row" key={p.id}><span>Payment</span><strong>{statusLabel(p.status)} · {money(p.amount,p.currency)}</strong><small>{p.provider}{p.method ? " · " + p.method : ""}</small></div>) : <div className="admin-message">No payment record.</div>}
            {detail.shipments.length ? detail.shipments.map(s=><div className="admin-mini-row" key={s.id}><span>Shipment</span><strong>{statusLabel(s.status)}</strong><small>{s.provider || "Unassigned"}{s.tracking_number ? " · " + s.tracking_number : ""}</small></div>) : <div className="admin-message">No shipment record.</div>}
          </div>
        </section>

        <section className="admin-panel admin-order-wide">
          <h2>Status history</h2><p className="admin-panel-sub">Order workflow audit trail.</p>
          <div className="admin-history">{detail.history.map(h=><div className="admin-history-row" key={h.id}><span>{date(h.created_at)}</span><strong>{h.from_status ? statusLabel(h.from_status) + " → " : ""}{statusLabel(h.to_status)}</strong><small>{h.changed_by}{h.note ? " · " + h.note : ""}</small></div>)}</div>
        </section>
      </div>
    </main>;
  }

  return <main className="admin-page">
    <div className="admin-page-head">
      <div><span className="eyebrow">ORDER OPERATIONS</span><h1>Orders</h1><p>Search recent orders, inspect line items and move fulfilment status safely.</p></div>
      <button className="button button-dark" onClick={load} disabled={loading}>{loading ? <LoaderCircle size={15} className="spin"/> : <RefreshCw size={15}/>} Refresh</button>
    </div>
    {(message || error) && <div className={"admin-message " + (error ? "error":"success")} style={{marginBottom:14}}>{error || message}</div>}
    <section className="admin-kpi-grid admin-kpi-grid-compact">
      <div className="admin-kpi-card"><span>Orders in view</span><strong>{totals.count}</strong></div>
      <div className="admin-kpi-card"><span>Units in view</span><strong>{totals.units}</strong></div>
      <div className="admin-kpi-card"><span>Gross value</span><strong>{money(totals.value)}</strong></div>
      <div className="admin-kpi-card"><span>Result cap</span><strong>200</strong></div>
    </section>
    <section className="admin-panel">
      <div className="admin-toolbar admin-listing-toolbar">
        <div className="listing-search admin-search-wide"><Search size={14}/><input value={query} onChange={e=>setQuery(e.target.value)} onKeyDown={e=>e.key==="Enter" && load()} placeholder="Search order number, customer, phone or email…"/></div>
        <select className="listing-select admin-filter-select" value={status} onChange={e=>setStatus(e.target.value)}>{STATUSES.map(([value,label])=><option key={value} value={value}>{label}</option>)}</select>
        <button className="button" onClick={load}>Search</button>
      </div>
      {loading ? <div className="admin-loading"><LoaderCircle className="spin"/><span>Loading orders…</span></div> :
       orders.length===0 ? <div className="admin-message">No matching orders found.</div> :
       <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Order</th><th>Placed</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
       <tbody>{orders.map(order=><tr key={order.id} className="admin-click-row" onClick={()=>openOrder(order.id)}>
         <td><strong>{order.order_number}</strong></td><td>{date(order.placed_at || order.created_at)}</td>
         <td><strong>{order.customer_name}</strong><br/><span className="admin-table-muted">{order.customer_phone || order.customer_email || "—"}</span></td>
         <td>{order.item_count} units · {order.line_count} lines</td><td><strong>{money(order.grand_total)}</strong></td>
         <td><span className="admin-status-dot">{statusLabel(order.status)}</span></td><td><ChevronRight size={15}/></td>
       </tr>)}</tbody></table></div>}
    </section>
  </main>;
}

<style>
.item-subtotal-cell { font-weight:700; color:var(--primary-600); font-variant-numeric:tabular-nums; white-space:nowrap; }
.items-tbl td { padding:12px; }
.item-price { text-align:right; font-variant-numeric:tabular-nums; }

/* Paket kaca film mobil */
.pack-card { border:1px solid var(--border-color); border-left:4px solid var(--primary-600); border-radius:10px; padding:14px; background:var(--white); }
.pack-card .pack-label { font-size:12px; font-weight:600; color:var(--neutral-600); margin-bottom:4px; display:block; }
.pack-head { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--neutral-500); margin:14px 0 6px; }
.pack-head::after { content:""; flex:1; height:1px; background:var(--border-color); }
.pack-cols { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1fr) 36px; gap:8px; font-size:11.5px; font-weight:600; color:var(--neutral-500); margin-bottom:4px; }
.pack-line { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1fr) 36px; gap:8px; align-items:center; margin-bottom:8px; }
.pack-line .remove-line { width:36px; height:36px; padding:0; display:inline-flex; align-items:center; justify-content:center; }
.pack-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
.pack-chip { display:inline-flex; align-items:center; gap:6px; background:var(--primary-50, #eff6ff); color:var(--primary-700, #1d4ed8); border-radius:999px; padding:3px 10px; font-size:12px; font-weight:600; }
.pack-chip small { font-weight:500; color:var(--neutral-600); }
.pack-legacy { margin-top:10px; font-size:12px; color:#92400e; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:6px 10px; }
.price-wrap { position:relative; }
.price-wrap > span { position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--neutral-500); pointer-events:none; }
.price-wrap > .item-price { padding-left:32px; }
@media (max-width: 767.98px) {
  .pack-cols { display:none; }
  .pack-line { grid-template-columns:1fr 36px; }
  .pack-line .line-part { grid-column:1 / -1; }
  .pack-line .line-type, .pack-line .line-product { grid-column:1 / 2; }
  .pack-line .remove-line { grid-row:2 / 4; grid-column:2; align-self:center; }
}
[data-theme="dark"] .pack-card { background:var(--dark-2); }
</style>

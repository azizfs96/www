<style>
    .soc-page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .soc-page-title { font-size: 30px; font-weight: 800; color: #eef2ff; letter-spacing: -0.4px; margin: 0 0 6px; }
    .soc-page-subtitle { color: #9ca3af; font-size: 13px; }
    .soc-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; border: 1px solid rgba(157,78,221,.45); color: #c4b5fd; background: rgba(157,78,221,.12); font-size: 11px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; }

    .soc-panel { background: linear-gradient(180deg, #111113 0%, #0b0b0d 100%); border: 1px solid rgba(255,255,255,.16); border-radius: 14px; box-shadow: 0 12px 30px rgba(0,0,0,.35); }
    .soc-panel-head { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,.08); display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .soc-panel-title { color: #e5e7eb; font-size: 14px; font-weight: 700; letter-spacing: .3px; }
    .soc-panel-sub { color: #9ca3af; font-size: 12px; }
    .soc-panel-body { padding: 14px 16px; }

    .soc-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
    .soc-kpi-card { padding: 14px; border-radius: 12px; border: 1px solid rgba(255,255,255,.14); background: rgba(255,255,255,.02); }
    .soc-kpi-label { color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
    .soc-kpi-value { color: #f3f4f6; font-weight: 800; font-size: 30px; line-height: 1; margin: 8px 0; }
    .soc-kpi-trend { color: #c4b5fd; font-size: 12px; }
    .soc-kpi-exec { border-color: rgba(168,85,247,.38); background: radial-gradient(circle at top right, rgba(168,85,247,.22), rgba(255,255,255,.02) 48%); box-shadow: 0 10px 24px rgba(124,58,237,.18); }
    .soc-kpi-exec .soc-kpi-value { color: #f5f3ff; font-size: 32px; letter-spacing: -0.3px; }
    .soc-kpi-note-neg { color: #fca5a5; font-size: 12px; font-weight: 600; }

    .soc-two-col { display: grid; grid-template-columns: 1.2fr .8fr; gap: 12px; }
    @media (max-width: 1100px) { .soc-two-col { grid-template-columns: 1fr; } }

    .soc-table-wrap { overflow-x: auto; }
    .soc-table { width: 100%; border-collapse: collapse; }
    .soc-table th, .soc-table td { text-align: left; padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,.08); }
    .soc-table th { color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
    .soc-table td { color: #e5e7eb; font-size: 13px; }
    .soc-table tr:hover td { background: rgba(157,78,221,.06); }

    .soc-pill { font-size: 11px; padding: 4px 8px; border-radius: 999px; border: 1px solid transparent; white-space: nowrap; }
    .soc-high { color: #f87171; border-color: rgba(248,113,113,.42); background: rgba(248,113,113,.14); box-shadow: 0 0 0 1px rgba(248,113,113,.15), 0 0 14px rgba(239,68,68,.22); }
    .soc-medium { color: #fbbf24; border-color: rgba(251,191,36,.35); background: rgba(251,191,36,.12); }
    .soc-low { color: #4ade80; border-color: rgba(74,222,128,.35); background: rgba(74,222,128,.12); }
    .soc-open { color: #f3f4f6; border-color: rgba(255,255,255,.25); background: rgba(255,255,255,.06); }
    .soc-progress { position: relative; height: 8px; border-radius: 999px; background: rgba(255,255,255,.09); overflow: hidden; }
    .soc-progress > span { position: absolute; left: 0; top: 0; bottom: 0; background: linear-gradient(90deg, #7c3aed, #a855f7); }
</style>

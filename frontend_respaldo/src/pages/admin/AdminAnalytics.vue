<script setup>
import { adminDashboardMock } from "../../mocks/rewards.js"
const data = adminDashboardMock
const maxVal = Math.max(...data.chartData.issued)
function barH(v) { return Math.round((v / maxVal) * 100) }
function fmt(n) { if(n>=1000) return (n/1000).toFixed(0)+"K"; return n }
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">Dashboards y analítica</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Métricas avanzadas del programa de recompensas.</p></div>
    <div class="kpis">
      <div class="kpi card"><div class="kl">Puntos emitidos</div><div class="kv blue">{{ data.pointsIssuedMonth.toLocaleString() }}</div></div>
      <div class="kpi card"><div class="kl">Canjes completados</div><div class="kv green">{{ data.completedRedemptions.toLocaleString() }}</div></div>
      <div class="kpi card"><div class="kl">Alertas activas</div><div class="kv red">{{ data.fraudAlerts }}</div></div>
      <div class="kpi card"><div class="kl">Negocios activos</div><div class="kv">{{ data.adheredBusinesses }}</div></div>
    </div>
    <div class="grid-2">
      <div class="card">
        <div class="ch"><span class="ct">Emisión vs. Canje (6 meses)</span><div class="legend"><span class="li"><span class="ld" style="background:#2878ED"></span>Emitidos</span><span class="li"><span class="ld" style="background:#93C5FD"></span>Canjeados</span></div></div>
        <div class="bar-chart">
          <div class="y-axis"><span>250K</span><span>200K</span><span>150K</span><span>100K</span><span>50K</span><span>0</span></div>
          <div class="bars-area">
            <div v-for="(label,i) in data.chartData.labels" :key="label" class="bar-group">
              <div class="bar-pair">
                <div class="bar" style="background:#2878ED;border-radius:3px 3px 0 0" :style="{height:barH(data.chartData.issued[i])+'%'}"></div>
                <div class="bar" style="background:#93C5FD;border-radius:3px 3px 0 0" :style="{height:barH(data.chartData.redeemed[i])+'%'}"></div>
              </div>
              <div class="blabel">{{ label }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="ch"><span class="ct">Participación por tipo de fuente</span></div>
        <div class="donut-placeholder"><div class="dp-inner"><div class="dp-pct">68%</div><div class="dp-label">Activos</div></div></div>
        <div class="source-list">
          <div class="si"><div class="sbar" style="background:#2878ED;width:60%"></div><span>Compras (60%)</span></div>
          <div class="si"><div class="sbar" style="background:#16a34a;width:25%"></div><span>Eventos (25%)</span></div>
          <div class="si"><div class="sbar" style="background:#d97706;width:15%"></div><span>Encuestas (15%)</span></div>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="ch"><span class="ct">Top recompensas más canjeadas</span></div>
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>#</th><th>Recompensa</th><th>Negocio</th><th>Canjes</th><th>Puntos consumidos</th></tr></thead>
          <tbody>
            <tr><td>1</td><td>☕ Café Americano</td><td>Café Campus</td><td>842</td><td style="color:var(--cd-primary);font-weight:600">126,300 pts</td></tr>
            <tr><td>2</td><td>📓 Libreta Campus</td><td>Librería Campus</td><td>520</td><td style="color:var(--cd-primary);font-weight:600">156,000 pts</td></tr>
            <tr><td>3</td><td>🥤 Termo Campus</td><td>Tienda Campus</td><td>310</td><td style="color:var(--cd-primary);font-weight:600">248,000 pts</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.ph{display:flex;flex-direction:column;gap:2px;}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
.kpi{padding:18px 20px;text-align:center;}
.kl{font-size:12px;color:var(--cd-text-secondary);margin-bottom:6px;}
.kv{font-size:22px;font-weight:700;color:var(--cd-text-primary);}
.blue{color:var(--cd-primary);} .green{color:#16a34a;} .red{color:#dc2626;}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.ch{display:flex;align-items:center;justify-content:space-between;padding:16px 18px 12px;border-bottom:1px solid var(--cd-border-light);}
.ct{font-size:14px;font-weight:700;color:var(--cd-text-primary);}
.legend{display:flex;gap:12px;} .li{display:flex;align-items:center;gap:5px;font-size:12px;color:var(--cd-text-secondary);}
.ld{width:10px;height:10px;border-radius:50%;}
.bar-chart{display:flex;padding:12px 18px 12px 8px;height:200px;gap:8px;}
.y-axis{display:flex;flex-direction:column;justify-content:space-between;font-size:10px;color:var(--cd-text-muted);padding-bottom:20px;width:36px;flex-shrink:0;text-align:right;}
.bars-area{flex:1;display:flex;align-items:flex-end;gap:6px;}
.bar-group{flex:1;display:flex;flex-direction:column;align-items:center;}
.bar-pair{width:100%;display:flex;gap:3px;align-items:flex-end;height:155px;}
.bar{flex:1;min-height:2px;transition:height 0.4s;}
.blabel{font-size:11px;color:var(--cd-text-muted);margin-top:4px;}
.donut-placeholder{display:flex;align-items:center;justify-content:center;padding:20px;}
.dp-inner{width:100px;height:100px;border-radius:50%;border:12px solid var(--cd-primary);display:flex;flex-direction:column;align-items:center;justify-content:center;}
.dp-pct{font-size:18px;font-weight:700;color:var(--cd-primary);}
.dp-label{font-size:11px;color:var(--cd-text-muted);}
.source-list{padding:0 18px 16px;display:flex;flex-direction:column;gap:8px;}
.si{display:flex;align-items:center;gap:10px;font-size:12px;color:var(--cd-text-secondary);}
.sbar{height:8px;border-radius:4px;flex-shrink:0;}
@media(max-width:1100px){.kpis,.grid-2{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){.kpis{grid-template-columns:1fr 1fr;}}
</style>

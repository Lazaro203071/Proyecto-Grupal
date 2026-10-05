<script setup>
import { ref } from "vue"
import EmptyState from "../../components/ui/EmptyState.vue"
import { auditMock } from "../../mocks/rewards.js"
const search = ref("")
const filtered = ref(auditMock)
function applySearch() {
  const q = search.value.toLowerCase()
  filtered.value = q ? auditMock.filter(a => a.user.toLowerCase().includes(q) || a.action.toLowerCase().includes(q) || a.entity.toLowerCase().includes(q)) : auditMock
}
const colors = {"LM":"#16a34a","JR":"#2878ED","SC":"#d97706","AR":"#7c3aed"}
function ac(initials){ return colors[initials] || "#2878ED" }
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">Auditoría transversal</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Registro completo de eventos y acciones del sistema.</p></div>
    <div class="search-bar"><input class="cd-input" v-model="search" @input="applySearch" placeholder="Buscar por usuario, acción o entidad…" style="max-width:400px"/></div>
    <div class="card">
      <EmptyState v-if="!filtered.length" title="Sin resultados" message="No se encontraron registros de auditoría." icon="🔍" />
      <div v-else class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Usuario</th><th>Acción</th><th>Entidad</th><th>Fecha</th><th>IP</th><th>Correlación</th></tr></thead>
          <tbody>
            <tr v-for="a in filtered" :key="a.id">
              <td><div style="display:flex;align-items:center;gap:8px"><div class="av" :style="{background:ac(a.initials)}">{{a.initials}}</div>{{a.user}}</div></td>
              <td>{{a.action}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{a.entity}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px;white-space:nowrap">{{a.date}}</td>
              <td><code style="font-size:11px;background:#f0f3f9;padding:2px 6px;border-radius:4px">{{a.ip}}</code></td>
              <td><code style="font-size:11px;background:#f0f3f9;padding:2px 6px;border-radius:4px">{{a.correlation}}</code></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style scoped>
.page,.ph{display:flex;flex-direction:column;gap:20px;}
.ph{gap:2px;margin-bottom:2px;}
.search-bar{display:flex;gap:10px;}
.av{width:28px;height:28px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;}
</style>

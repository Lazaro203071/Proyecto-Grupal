<script setup>
import { ref } from "vue"
import StatusBadge from "../../components/ui/StatusBadge.vue"
import ConfirmModal from "../../components/ui/ConfirmModal.vue"
import { useToast } from "../../composables/useToast.js"
import { earningRulesMock } from "../../mocks/rewards.js"
const { success } = useToast()
const showModal = ref(false)
function createRule() { showModal.value = false; success("Nueva regla creada exitosamente.") }
</script>
<template>
  <div class="page">
    <div class="phrow">
      <div class="ph"><h1 class="page-title">Reglas de acumulación</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Define cómo se acumulan puntos en la plataforma.</p></div>
      <button class="btn btn-primary" @click="showModal=true">＋ Nueva regla</button>
    </div>
    <div class="rules-grid">
      <div v-for="r in earningRulesMock" :key="r.id" class="rule-card card">
        <div class="rc-header">
          <div class="rc-value">{{ r.value }}</div>
          <StatusBadge :status="r.status" />
        </div>
        <div class="rc-name">{{ r.name }}</div>
        <div class="rc-desc">{{ r.description }}</div>
        <div class="rc-meta">
          <span>🌐 {{ r.scope }}</span>
          <span v-if="r.maxPerOp">📦 Máx. {{ r.maxPerOp }} pts/op</span>
        </div>
        <button class="btn btn-ghost btn-sm" style="margin-top:8px;width:100%;justify-content:center">Editar</button>
      </div>
    </div>
    <ConfirmModal :show="showModal" title="Nueva regla de acumulación" message="¿Deseas crear una nueva regla de acumulación? Podrás configurarla a continuación." confirm-label="Crear regla" @confirm="createRule" @cancel="showModal=false" />
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.phrow{display:flex;align-items:flex-end;justify-content:space-between;}
.ph{display:flex;flex-direction:column;gap:2px;}
.rules-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;}
.rule-card{padding:18px;display:flex;flex-direction:column;gap:6px;}
.rc-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;}
.rc-value{font-size:20px;font-weight:800;color:var(--cd-primary);}
.rc-name{font-size:14px;font-weight:700;color:var(--cd-text-primary);}
.rc-desc{font-size:12px;color:var(--cd-text-secondary);}
.rc-meta{display:flex;gap:12px;font-size:11px;color:var(--cd-text-muted);}
</style>

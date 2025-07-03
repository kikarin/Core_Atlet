<script setup lang="ts">
import { computed, type PropType } from 'vue';
import type { Column } from './datatable/types';

const props = defineProps({
  columns: { type: Array as PropType<Column[]>, required: true },
  rows: { type: Array as PropType<any[]>, required: true },
  noDataText: { type: String, default: 'Tidak ada data.' },
});

const visibleColumns = computed(() => props.columns.filter(col => col.visible !== false));
</script>

<template>
  <div class="space-y-4 -mt-6">
    <div>
      <slot name="title" />
    </div>
    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full border text-sm">
        <thead>
          <tr class="bg-muted">
            <th class="px-2 py-2 border w-12 text-center">#</th>
            <th v-for="col in visibleColumns" :key="col.key" class="px-3 py-2 border">{{ col.label }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="rows.length === 0">
            <td :colspan="visibleColumns.length + 1" class="text-center text-muted-foreground py-4">{{ noDataText }}</td>
          </tr>
          <tr v-for="(row, idx) in rows" :key="row.id">
            <td class="px-2 py-2 border text-center">{{ idx + 1 }}</td>
            <td v-for="col in visibleColumns" :key="col.key" class="px-3 py-2 border">
              <span v-if="typeof col.format === 'function'" v-html="col.format(row)"></span>
              <span v-else>{{ row[col.key] }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template> 
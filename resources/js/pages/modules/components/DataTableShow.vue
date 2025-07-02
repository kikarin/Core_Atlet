<script setup lang="ts">
import { computed, ref, watch, onMounted, onBeforeUnmount, type PropType } from 'vue';
import RowActions from '@/pages/modules/components/tables/RowActions.vue';
import { Button } from '@/components/ui/button';
import { useDataTable } from './datatable/useDataTable';
import type { Column } from './datatable/types';

const props = defineProps({
  columns: { type: Array as PropType<Column[]>, required: true },
  rows: { type: Array as PropType<any[]>, required: true },
  selected: { type: Array as PropType<number[]>, default: () => [] },
  actions: { type: Function as PropType<(row: any) => { label: string; onClick: () => void; }[]>, default: () => [] },
  showCheckbox: { type: Boolean, default: true },
  showActions: { type: Boolean, default: true },
  noDataText: { type: String, default: 'Tidak ada data.' },
});

const emit = defineEmits(['update:selected', 'edit', 'delete', 'deleteSelected']);

const { visibleColumns, toggleSelect, toggleSelectAll } = useDataTable({
  columns: props.columns,
  rows: props.rows,
  selected: props.selected,
  total: props.rows.length,
  search: '',
  sort: { key: '', order: 'asc' },
  page: 1,
  perPage: props.rows.length || 10,
  hidePagination: true,
}, (event: string, ...args: any[]) => emit(event, ...args));

const selectedLocal = ref<number[]>([...props.selected]);

// Sync selected prop <-> local
watch(() => props.selected, (val) => {
  if (JSON.stringify(val) !== JSON.stringify(selectedLocal.value)) {
    selectedLocal.value = [...val];
  }
});
watch(selectedLocal, (val) => {
  if (JSON.stringify(val) !== JSON.stringify(props.selected)) {
    emit('update:selected', val);
  }
});

const allSelected = computed(() => props.rows.length > 0 && selectedLocal.value.length === props.rows.length);

// Keyboard shortcut: Ctrl+A/Cmd+A
const handleKeydown = (e: KeyboardEvent) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'a') {
    e.preventDefault();
    if (selectedLocal.value.length === props.rows.length) {
      selectedLocal.value = [];
    } else {
      selectedLocal.value = props.rows.map((row) => row.id);
    }
  }
};
onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});
onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown);
});

function handleDeleteSelected() {
  emit('deleteSelected', [...selectedLocal.value]);
}
</script>

<template>
  <div class="space-y-4 -mt-6">
    <div class="flex items-center justify-between mb-2">
      <div>
        <slot name="title">
          <h3 class="font-semibold text-base text-muted-foreground uppercase tracking-wide">
            Data Tabel
            <span class="block mt-1 text-xs font-normal text-muted-foreground">
              (Shortcut: <kbd class="px-1 py-0.5 bg-muted rounded">Ctrl+A</kbd> / <kbd class="px-1 py-0.5 bg-muted rounded">Cmd+A</kbd> untuk Select All)
            </span>
          </h3>
        </slot>
      </div>
      <div class="flex items-center gap-2">
        <slot name="actions">
          <Button variant="destructive" size="sm" :disabled="selectedLocal.length === 0" @click="handleDeleteSelected">
            Delete Selected ({{ selectedLocal.length }})
          </Button>
        </slot>
      </div>
    </div>
    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full border text-sm">
        <thead>
          <tr class="bg-muted">
            <th v-if="showCheckbox" class="px-2 py-2 border w-8 text-center">
              <label class="bg-background relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-gray-500">
                <input
                  type="checkbox"
                  class="peer sr-only"
                  :checked="allSelected"
                  @change="(e) => toggleSelectAll((e.target as HTMLInputElement).checked)"
                />
                <div class="bg-primary h-3 w-3 scale-0 transform rounded-sm transition-all peer-checked:scale-100"></div>
              </label>
            </th>
            <th class="px-2 py-2 border w-12 text-center">#</th>
            <th v-for="col in visibleColumns" :key="col.key" class="px-3 py-2 border">{{ col.label }}</th>
            <th v-if="showActions" class="px-2 py-2 border w-12 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="rows.length === 0">
            <td :colspan="visibleColumns.length + (showCheckbox ? 2 : 1)" class="text-center text-muted-foreground py-4">{{ noDataText }}</td>
          </tr>
          <tr v-for="(row, idx) in rows" :key="row.id">
            <td v-if="showCheckbox" class="px-2 py-2 border text-center">
              <label class="bg-background relative inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-gray-500">
                <input
                  type="checkbox"
                  class="peer sr-only"
                  :checked="selectedLocal.includes(row.id)"
                  @change="(e) => toggleSelect(row.id)"
                />
                <svg
                  class="text-primary h-4 w-4 scale-75 opacity-0 transition-all duration-200 peer-checked:scale-100 peer-checked:opacity-100"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </label>
            </td>
            <td class="px-2 py-2 border text-center">{{ idx + 1 }}</td>
            <td v-for="col in visibleColumns" :key="col.key" class="px-3 py-2 border">
              <span v-if="typeof col.format === 'function'" v-html="col.format(row)"></span>
              <span v-else>{{ row[col.key] }}</span>
            </td>
            <td v-if="showActions" class="px-2 py-2 border text-center">
              <RowActions :id="row.id" :actions="actions(row)" baseUrl="" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template> 
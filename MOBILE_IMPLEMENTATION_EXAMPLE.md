# Contoh Implementasi Mobile untuk Program Latihan CRUD

## 1. Composable untuk Program Latihan

```typescript
// composables/useProgramLatihan.ts
import { ref, computed } from 'vue'

export const useProgramLatihan = () => {
  const programs = ref([])
  const caborList = ref([])
  const caborKategoriList = ref([])
  const loading = ref(false)
  const error = ref('')

  // Fetch programs
  const fetchPrograms = async (params = {}) => {
    try {
      loading.value = true
      error.value = ''
      
      const queryParams = new URLSearchParams(params).toString()
      const response = await $fetch(`/api/program-latihan/mobile?${queryParams}`, {
        headers: {
          'Authorization': `Bearer ${useCookie('token').value}`
        }
      })
      
      programs.value = response.data
    } catch (err) {
      error.value = err.data?.message || 'Gagal mengambil data program latihan'
      console.error('Error fetching programs:', err)
    } finally {
      loading.value = false
    }
  }

  // Fetch cabor list
  const fetchCaborList = async () => {
    try {
      const response = await $fetch('/api/program-latihan/cabor/list', {
        headers: {
          'Authorization': `Bearer ${useCookie('token').value}`
        }
      })
      caborList.value = response.data
    } catch (err) {
      console.error('Error fetching cabor list:', err)
    }
  }

  // Fetch cabor kategori by cabor ID
  const fetchCaborKategori = async (caborId) => {
    try {
      const response = await $fetch(`/api/program-latihan/cabor/${caborId}/kategori`, {
        headers: {
          'Authorization': `Bearer ${useCookie('token').value}`
        }
      })
      caborKategoriList.value = response.data
    } catch (err) {
      console.error('Error fetching cabor kategori:', err)
    }
  }

  // Create program
  const createProgram = async (data) => {
    try {
      loading.value = true
      error.value = ''
      
      const response = await $fetch('/api/program-latihan', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${useCookie('token').value}`,
          'Content-Type': 'application/json'
        },
        body: data
      })
      
      // Add to programs list
      programs.value.unshift(response.data)
      return response
    } catch (err) {
      error.value = err.data?.message || 'Gagal membuat program latihan'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update program
  const updateProgram = async (id, data) => {
    try {
      loading.value = true
      error.value = ''
      
      const response = await $fetch(`/api/program-latihan/${id}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${useCookie('token').value}`,
          'Content-Type': 'application/json'
        },
        body: data
      })
      
      // Update in programs list
      const index = programs.value.findIndex(p => p.id === id)
      if (index > -1) {
        programs.value[index] = response.data
      }
      
      return response
    } catch (err) {
      error.value = err.data?.message || 'Gagal memperbarui program latihan'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete program
  const deleteProgram = async (id) => {
    try {
      loading.value = true
      error.value = ''
      
      await $fetch(`/api/program-latihan/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${useCookie('token').value}`
        }
      })
      
      // Remove from programs list
      const index = programs.value.findIndex(p => p.id === id)
      if (index > -1) {
        programs.value.splice(index, 1)
      }
    } catch (err) {
      error.value = err.data?.message || 'Gagal menghapus program latihan'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    programs,
    caborList,
    caborKategoriList,
    loading,
    error,
    fetchPrograms,
    fetchCaborList,
    fetchCaborKategori,
    createProgram,
    updateProgram,
    deleteProgram
  }
}
```

## 2. Form Component untuk Create/Edit

```vue
<!-- components/ProgramLatihanForm.vue -->
<template>
  <div class="space-y-6">
    <!-- Cabor Selection -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Cabor <span class="text-red-500">*</span>
      </label>
      <select
        v-model="formData.cabor_id"
        @change="onCaborChange"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#597BF9] focus:border-transparent"
        :disabled="loading"
      >
        <option value="">Pilih Cabor</option>
        <option
          v-for="cabor in caborList"
          :key="cabor.id"
          :value="cabor.id"
        >
          {{ cabor.nama }}
        </option>
      </select>
      <p v-if="errors.cabor_id" class="mt-1 text-sm text-red-600">
        {{ errors.cabor_id }}
      </p>
    </div>

    <!-- Kategori Selection -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Kategori <span class="text-red-500">*</span>
      </label>
      <select
        v-model="formData.cabor_kategori_id"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#597BF9] focus:border-transparent"
        :disabled="!formData.cabor_id || loading"
      >
        <option value="">Pilih Kategori</option>
        <option
          v-for="kategori in caborKategoriList"
          :key="kategori.id"
          :value="kategori.id"
        >
          {{ kategori.nama }}
        </option>
      </select>
      <p v-if="errors.cabor_kategori_id" class="mt-1 text-sm text-red-600">
        {{ errors.cabor_kategori_id }}
      </p>
    </div>

    <!-- Nama Program -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Nama Program <span class="text-red-500">*</span>
      </label>
      <input
        v-model="formData.nama_program"
        type="text"
        placeholder="Masukkan nama program latihan"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#597BF9] focus:border-transparent"
        :disabled="loading"
      />
      <p v-if="errors.nama_program" class="mt-1 text-sm text-red-600">
        {{ errors.nama_program }}
      </p>
    </div>

    <!-- Periode Mulai -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Periode Mulai <span class="text-red-500">*</span>
      </label>
      <input
        v-model="formData.periode_mulai"
        type="date"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#597BF9] focus:border-transparent"
        :disabled="loading"
      />
      <p v-if="errors.periode_mulai" class="mt-1 text-sm text-red-600">
        {{ errors.periode_mulai }}
      </p>
    </div>

    <!-- Periode Selesai -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Periode Selesai <span class="text-red-500">*</span>
      </label>
      <input
        v-model="formData.periode_selesai"
        type="date"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#597BF9] focus:border-transparent"
        :disabled="loading"
        :min="formData.periode_mulai"
      />
      <p v-if="errors.periode_selesai" class="mt-1 text-sm text-red-600">
        {{ errors.periode_selesai }}
      </p>
    </div>

    <!-- Keterangan -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Keterangan <span class="text-gray-400 text-xs">(Opsional)</span>
      </label>
      <textarea
        v-model="formData.keterangan"
        rows="3"
        placeholder="Masukkan keterangan program latihan (opsional)"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#597BF9] focus:border-transparent resize-none"
        :disabled="loading"
      ></textarea>
      <p v-if="errors.keterangan" class="mt-1 text-sm text-red-600">
        {{ errors.keterangan }}
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 pt-4">
      <button
        @click="$emit('cancel')"
        type="button"
        class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
        :disabled="loading"
      >
        Batal
      </button>
      <button
        @click="handleSubmit"
        type="button"
        class="flex-1 px-4 py-2 bg-[#597BF9] text-white rounded-lg hover:bg-[#4c6ef5] transition-colors disabled:opacity-50"
        :disabled="loading || !isFormValid"
      >
        <span v-if="loading" class="flex items-center justify-center">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
          Menyimpan...
        </span>
        <span v-else>{{ isEdit ? 'Update' : 'Simpan' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useProgramLatihan } from '~/composables/useProgramLatihan'

interface FormData {
  cabor_id: number | null
  cabor_kategori_id: number | null
  nama_program: string
  periode_mulai: string
  periode_selesai: string
  keterangan: string
}

interface Props {
  initialData?: Partial<FormData>
  loading?: boolean
  isEdit?: boolean
}

interface Emits {
  (e: 'submit', data: FormData): void
  (e: 'cancel'): void
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  isEdit: false
})

const emit = defineEmits<Emits>()

const { caborList, caborKategoriList, fetchCaborList, fetchCaborKategori } = useProgramLatihan()

// Form data
const formData = ref<FormData>({
  cabor_id: null,
  cabor_kategori_id: null,
  nama_program: '',
  periode_mulai: '',
  periode_selesai: '',
  keterangan: ''
})

// Errors
const errors = ref<Partial<Record<keyof FormData, string>>>({})

// Computed
const isFormValid = computed(() => {
  return !!(
    formData.value.cabor_id &&
    formData.value.cabor_kategori_id &&
    formData.value.nama_program &&
    formData.value.periode_mulai &&
    formData.value.periode_selesai
  )
})

// Methods
const onCaborChange = async () => {
  // Reset kategori when cabor changes
  formData.value.cabor_kategori_id = null
  errors.value.cabor_kategori_id = ''
  
  // Fetch kategori for selected cabor
  if (formData.value.cabor_id) {
    await fetchCaborKategori(formData.value.cabor_id)
  }
}

const validateForm = (): boolean => {
  errors.value = {}
  let isValid = true

  if (!formData.value.cabor_id) {
    errors.value.cabor_id = 'Cabor harus dipilih'
    isValid = false
  }

  if (!formData.value.cabor_kategori_id) {
    errors.value.cabor_kategori_id = 'Kategori harus dipilih'
    isValid = false
  }

  if (!formData.value.nama_program.trim()) {
    errors.value.nama_program = 'Nama program harus diisi'
    isValid = false
  }

  if (!formData.value.periode_mulai) {
    errors.value.periode_mulai = 'Periode mulai harus diisi'
    isValid = false
  }

  if (!formData.value.periode_selesai) {
    errors.value.periode_selesai = 'Periode selesai harus diisi'
    isValid = false
  }

  if (formData.value.periode_mulai && formData.value.periode_selesai) {
    const startDate = new Date(formData.value.periode_mulai)
    const endDate = new Date(formData.value.periode_selesai)
    
    if (startDate >= endDate) {
      errors.value.periode_selesai = 'Periode selesai harus setelah periode mulai'
      isValid = false
    }
  }

  return isValid
}

const handleSubmit = () => {
  if (!validateForm()) return
  
  emit('submit', { ...formData.value })
}

// Watch for initial data changes
watch(() => props.initialData, (newData) => {
  if (newData) {
    formData.value = { ...formData.value, ...newData }
    
    // If cabor is selected, fetch its kategori
    if (newData.cabor_id) {
      fetchCaborKategori(newData.cabor_id)
    }
  }
}, { immediate: true, deep: true })

// Initialize form data
onMounted(async () => {
  await fetchCaborList()
  
  if (props.initialData) {
    formData.value = { ...formData.value, ...props.initialData }
    
    // If cabor is selected, fetch its kategori
    if (props.initialData.cabor_id) {
      await fetchCaborKategori(props.initialData.cabor_id)
    }
  }
})
</script>
```

## 3. Create Page

```vue
<!-- pages/program-latihan/create.vue -->
<template>
  <PageLayout>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-3 mb-2">
        <button
          @click="$router.back()"
          class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <h1 class="text-xl font-semibold text-gray-800">Tambah Program Latihan</h1>
      </div>
      <p class="text-gray-600 text-sm">Buat program latihan baru untuk atlet</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#597BF9]"></div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4"
    >
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
        <div class="flex-1">
          <p class="text-red-600 text-sm font-medium">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div v-else class="bg-white rounded-2xl p-6 shadow-sm">
      <ProgramLatihanForm
        :loading="submitting"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </div>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PageLayout from '~/components/PageLayout.vue'
import ProgramLatihanForm from '~/components/ProgramLatihanForm.vue'
import { useProgramLatihan } from '~/composables/useProgramLatihan'

const router = useRouter()
const { loading, error, createProgram } = useProgramLatihan()

const submitting = ref(false)

const handleSubmit = async (formData: any) => {
  try {
    submitting.value = true
    await createProgram(formData)
    
    // Show success message (you can add a toast notification here)
    console.log('Program latihan berhasil dibuat')
    
    // Redirect to program list
    router.push('/program-latihan')
  } catch (err) {
    console.error('Error creating program:', err)
  } finally {
    submitting.value = false
  }
}

const handleCancel = () => {
  router.push('/program-latihan')
}
</script>
```

## 4. Edit Page

```vue
<!-- pages/program-latihan/edit/[id].vue -->
<template>
  <PageLayout>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-3 mb-2">
        <button
          @click="$router.back()"
          class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <h1 class="text-xl font-semibold text-gray-800">Edit Program Latihan</h1>
      </div>
      <p class="text-gray-600 text-sm">Ubah informasi program latihan</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#597BF9]"></div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4"
    >
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
            clip-rule="evenodd"
          />
        </svg>
        <div class="flex-1">
          <p class="text-red-600 text-sm font-medium">{{ error }}</p>
        </div>
      </div>
      <div class="mt-3">
        <button
          @click="loadProgramData"
          class="px-3 py-1 text-red-700 underline text-sm hover:text-red-800"
        >
          Coba lagi
        </button>
      </div>
    </div>

    <!-- Form -->
    <div v-else-if="programData" class="bg-white rounded-2xl p-6 shadow-sm">
      <ProgramLatihanForm
        :initial-data="formData"
        :loading="submitting"
        :is-edit="true"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </div>

    <!-- Not Found -->
    <div v-else class="text-center py-12">
      <div class="text-gray-400 mb-4">
        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        Program tidak ditemukan
      </h3>
      <p class="text-gray-500 mb-4">
        Program latihan yang Anda cari tidak ditemukan atau telah dihapus.
      </p>
      <button
        @click="$router.push('/program-latihan')"
        class="px-4 py-2 bg-[#597BF9] text-white rounded-lg hover:bg-[#4c6ef5] transition-colors"
      >
        Kembali ke Daftar Program
      </button>
    </div>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import PageLayout from '~/components/PageLayout.vue'
import ProgramLatihanForm from '~/components/ProgramLatihanForm.vue'
import { useProgramLatihan } from '~/composables/useProgramLatihan'

const router = useRouter()
const route = useRoute()
const { programs, loading, error, updateProgram } = useProgramLatihan()

const programData = ref<any>(null)
const submitting = ref(false)

const programId = computed(() => parseInt(route.params.id as string))

const formData = computed(() => {
  if (!programData.value) return null

  return {
    cabor_id: programData.value.cabor?.id || null,
    cabor_kategori_id: programData.value.kategori?.id || null,
    nama_program: programData.value.nama_program || '',
    periode_mulai: programData.value.periode?.mulai || '',
    periode_selesai: programData.value.periode?.selesai || '',
    keterangan: programData.value.keterangan || ''
  }
})

const loadProgramData = async () => {
  try {
    // Untuk sementara, cari data dari array programs yang sudah ada
    const data = programs.value.find(p => p.id === programId.value)
    
    if (data) {
      programData.value = data
    }
  } catch (err) {
    console.error('Error loading program data:', err)
  }
}

const handleSubmit = async (formData: any) => {
  try {
    submitting.value = true
    await updateProgram(programId.value, formData)
    
    // Show success message (you can add a toast notification here)
    console.log('Program latihan berhasil diupdate')
    
    // Redirect to program list
    router.push('/program-latihan')
  } catch (err) {
    console.error('Error updating program:', err)
  } finally {
    submitting.value = false
  }
}

const handleCancel = () => {
  router.push('/program-latihan')
}

onMounted(async () => {
  await loadProgramData()
})
</script>
```

## 5. Update Index Page untuk Delete

```vue
<!-- pages/program-latihan/index.vue -->
<template>
  <PageLayout>
    <!-- ... existing template ... -->
    
    <!-- Menu Options -->
    <div class="relative">
      <button
        @click="activeMenu = activeMenu === program.id ? null : program.id"
        class="p-2 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer group"
        :class="{ 'bg-gray-100': activeMenu === program.id }"
      >
        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
        </svg>
      </button>
      
      <!-- Dropdown Menu -->
      <div
        v-if="activeMenu === program.id"
        class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200 z-[9999]"
        style="pointer-events: auto;"
      >
        <div class="py-2">
          <button
            @click="() => { activeMenu = null; router.push(`/program-latihan/edit/${program.id}`); }"
            class="flex items-center gap-3 w-full px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-200 cursor-pointer group"
          >
            <svg class="w-4 h-4 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span class="font-medium">Edit Program</span>
          </button>
          <button
            @click="() => { activeMenu = null; handleDeleteProgram(program.id, program.nama_program); }"
            class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-200 cursor-pointer group"
          >
            <svg class="w-4 h-4 flex-shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="font-medium">Hapus Program</span>
          </button>
        </div>
      </div>
    </div>
    
    <!-- ... rest of template ... -->
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageLayout from '~/components/PageLayout.vue'
import { useProgramLatihan } from '~/composables/useProgramLatihan'
import { useAuth } from '~/composables/useAuth'

const router = useRouter()
const { user, logout, isAuthenticated, initAuth } = useAuth()

// Use composable
const {
  programs,
  caborList,
  loading,
  error,
  currentPage,
  totalPages,
  totalItems,
  perPage,
  searchQuery,
  selectedCabor,
  startDate,
  endDate,
  filteredPrograms,
  fetchPrograms,
  fetchCaborList,
  deleteProgram
} = useProgramLatihan()

// ... existing code ...

const handleDeleteProgram = async (id: number, programName: string) => {
  console.log('Delete program clicked for ID:', id, 'Name:', programName)
  activeMenu.value = null
  
  if (confirm(`Apakah Anda yakin ingin menghapus program "${programName}"?`)) {
    try {
      await deleteProgram(id)
      console.log('Program berhasil dihapus')
    } catch (err) {
      console.error('Error deleting program:', err)
    }
  }
}

// ... rest of script ...
</script>
```

## 6. API Configuration

Pastikan untuk menambahkan base URL API di `nuxt.config.ts`:

```typescript
// nuxt.config.ts
export default defineNuxtConfig({
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api'
    }
  }
})
```

Dan buat plugin untuk axios:

```typescript
// plugins/api.client.ts
export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  
  $fetch.create({
    baseURL: config.public.apiBase,
    onRequest({ request, options }) {
      const token = useCookie('token').value
      if (token) {
        options.headers = {
          ...options.headers,
          'Authorization': `Bearer ${token}`
        }
      }
    },
    onResponseError({ response }) {
      if (response.status === 401) {
        // Redirect to login
        navigateTo('/login')
      }
    }
  })
})
```

## 7. Error Handling

Tambahkan composable untuk error handling:

```typescript
// composables/useErrorHandler.ts
export const useErrorHandler = () => {
  const showError = (message: string) => {
    // Implement your error notification here
    console.error(message)
    // You can use a toast library like vue-toastification
  }

  const showSuccess = (message: string) => {
    // Implement your success notification here
    console.log(message)
    // You can use a toast library like vue-toastification
  }

  return {
    showError,
    showSuccess
  }
}
```

## 8. Permission Check

Tambahkan composable untuk permission check:

```typescript
// composables/usePermission.ts
export const usePermission = () => {
  const { user } = useAuth()
  
  const canManageProgramLatihan = computed(() => {
    if (!user.value) return false
    
    const allowedRoles = [1, 11, 36] // Superadmin dan Pelatih
    return allowedRoles.includes(user.value.current_role_id)
  })

  return {
    canManageProgramLatihan
  }
}
```

Dan gunakan di template:

```vue
<template>
  <!-- FAB Create Program - hanya tampil jika user punya permission -->
  <button
    v-if="canManageProgramLatihan"
    @click="router.push('/program-latihan/create')"
    class="fixed bottom-24 left-1/2 -translate-x-1/2 bg-[#597BF9] text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center hover:bg-[#4c6ef5] transition-colors z-50 transform"
  >
    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
    </svg>
  </button>
</template>

<script setup>
const { canManageProgramLatihan } = usePermission()
</script>
```

Dengan implementasi ini, form mobile akan dapat:
1. Mengambil daftar cabor dari API
2. Mengambil daftar kategori berdasarkan cabor yang dipilih
3. Membuat program latihan baru
4. Mengedit program latihan yang sudah ada
5. Menghapus program latihan
6. Menangani error dengan baik
7. Memvalidasi form sebelum submit
8. Menampilkan loading state
9. Membatasi akses berdasarkan role user

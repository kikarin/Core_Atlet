<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-background px-4">
    <div class="max-w-md w-full text-center">
      <div class="flex justify-center mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-destructive" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" />
        </svg>
      </div>
      <h1 class="text-4xl font-bold mb-2">403 Forbidden</h1>
      <p class="text-muted-foreground mb-6">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
      <inertia-link href="/" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-white shadow transition hover:bg-primary/90">
        Kembali ke Dashboard
      </inertia-link>
    </div>
  </div>
</template>

<script setup lang="ts">
// Tidak perlu logic khusus
</script>

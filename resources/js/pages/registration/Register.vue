<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Recaptcha from '@/components/Recaptcha.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    recaptchaSiteKey?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    recaptcha_token: '',
});

const recaptchaRef = ref<InstanceType<typeof Recaptcha> | null>(null);
const recaptchaVerified = ref(false);

const handleRecaptchaVerified = (token: string) => {
    form.recaptcha_token = token;
    recaptchaVerified.value = true;
};

const handleRecaptchaExpired = () => {
    form.recaptcha_token = '';
    recaptchaVerified.value = false;
};

const handleRecaptchaError = () => {
    form.recaptcha_token = '';
    recaptchaVerified.value = false;
};

const submit = () => {
    if (props.recaptchaSiteKey && props.recaptchaSiteKey.trim() !== '') {
        if (!recaptchaVerified.value || !form.recaptcha_token) {
            form.setError('recaptcha_token', 'Harap verifikasi bahwa Anda bukan robot');
            return;
        }
    }

    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation', 'recaptcha_token');
            recaptchaVerified.value = false;
            recaptchaRef.value?.reset();
        },
        onError: () => {
            recaptchaRef.value?.reset();
            recaptchaVerified.value = false;
            form.recaptcha_token = '';
        },
    });
};
</script>

<template>
    <AuthBase title="Daftar Akun Baru" description="Buat akun untuk mendaftar sebagai peserta (Atlet, Pelatih, atau Tenaga Pendukung)">
        <Head title="Registrasi" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input 
                        id="email" 
                        type="email" 
                        required 
                        autofocus 
                        :tabindex="1" 
                        autocomplete="email" 
                        v-model="form.email" 
                        placeholder="email@example.com" 
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Konfirmasi Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Konfirmasi Password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <!-- reCAPTCHA v2 (dengan checkbox dan challenge default) -->
                <div v-if="recaptchaSiteKey && recaptchaSiteKey.trim() !== ''" class="flex justify-center">
                    <Recaptcha
                        ref="recaptchaRef"
                        :site-key="recaptchaSiteKey"
                        version="v2"
                        theme="light"
                        @verified="handleRecaptchaVerified"
                        @expired="handleRecaptchaExpired"
                        @error="handleRecaptchaError"
                    />
                </div>
                <InputError :message="form.errors.recaptcha_token" />

                <Button type="submit" class="mt-2 w-full" tabindex="4" :disabled="form.processing || (recaptchaSiteKey && recaptchaSiteKey.trim() !== '' && !recaptchaVerified)">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Daftar
                </Button>
            </div>

            <div class="text-muted-foreground text-center text-sm">
                Sudah punya akun?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="5">Masuk</TextLink>
            </div>
        </form>
    </AuthBase>
</template>


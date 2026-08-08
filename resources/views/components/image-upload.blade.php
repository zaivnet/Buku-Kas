@props([
    'name' => 'proof_image',
    'label' => 'Bukti Transaksi (Opsional)',
    'existingUrl' => null,
])

<div
    x-data="{
        previewUrl: @js($existingUrl),
        removeExisting: false,
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.previewUrl = URL.createObjectURL(file);
                this.removeExisting = false;
            }
        },
        removeImage() {
            this.previewUrl = null;
            this.removeExisting = true;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        }
    }"
    class="space-y-2"
>
    <label class="form-label font-medium text-neutral-text">
        {{ $label }}
        <span class="text-xs text-neutral-muted font-normal">(JPG, PNG, WEBP, maks. 2MB)</span>
    </label>

    {{-- Input hidden penanda hapus gambar lama --}}
    <input type="hidden" name="remove_proof" :value="removeExisting ? '1' : '0'" />

    {{-- Container Preview / Dropzone --}}
    <div class="flex items-start gap-4">
        {{-- Preview Box --}}
        <div x-show="previewUrl" class="relative group w-32 h-32 rounded-xl overflow-hidden border border-neutral-border bg-neutral-bg flex-shrink-0">
            <img :src="previewUrl" class="w-full h-full object-cover" alt="Preview Bukti Gambar" />
            <button
                type="button"
                x-on:click="removeImage()"
                class="absolute top-1.5 right-1.5 p-1 bg-danger text-white rounded-full opacity-90 hover:opacity-100 shadow transition-opacity"
                title="Hapus Gambar"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Dropzone Input --}}
        <div class="flex-1">
            <label
                for="{{ $name }}"
                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-neutral-border rounded-xl cursor-pointer bg-white hover:bg-neutral-bg/50 hover:border-primary transition-colors p-4 text-center"
            >
                <svg class="w-8 h-8 text-neutral-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs font-medium text-primary">Ambil Foto / Pilih Gambar</span>
                <span class="text-xs text-neutral-muted mt-0.5">(Kamera HP / Struk Galeri)</span>

                <input
                    x-ref="fileInput"
                    type="file"
                    name="{{ $name }}"
                    id="{{ $name }}"
                    accept="image/jpeg,image/png,image/webp"
                    capture="environment"
                    class="hidden"
                    x-on:change="handleFileChange($event)"
                />
            </label>
        </div>
    </div>

    @if($errors->has($name))
        <p class="form-error">{{ $errors->first($name) }}</p>
    @endif
</div>

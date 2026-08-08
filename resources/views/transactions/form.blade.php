@csrf

<input type="hidden" name="type" value="{{ $type }}" />

<div
    class="space-y-4"
    x-data="{
        rawAmount: '{{ old('amount', $transaction->amount ?? '') }}',
        formattedAmount: '',
        formatCurrency(value) {
            let digits = (value + '').replace(/[^0-9]/g, '');
            if (!digits) {
                this.rawAmount = '';
                this.formattedAmount = '';
                return;
            }
            this.rawAmount = parseInt(digits, 10);
            this.formattedAmount = 'Rp ' + new Intl.NumberFormat('id-ID').format(this.rawAmount);
        },
        init() {
            if (this.rawAmount) {
                this.formatCurrency(this.rawAmount);
            }
        }
    }"
>
    {{-- Tanggal --}}
    <x-form.input
        type="date"
        label="Tanggal Transaksi"
        name="date"
        :value="old('date', isset($transaction->date) ? $transaction->date->format('Y-m-d') : date('Y-m-d'))"
        max="{{ date('Y-m-d') }}"
        required
    />

    {{-- Kategori --}}
    <x-form.select
        label="Kategori Transaksi ({{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }})"
        name="category_id"
        placeholder="-- Pilih Kategori --"
        required
    >
        @foreach($categories as $categoryOption)
            <option value="{{ $categoryOption->id }}" {{ old('category_id', $transaction->category_id ?? '') == $categoryOption->id ? 'selected' : '' }}>
                {{ $categoryOption->name }} {{ !$categoryOption->is_active ? '(Nonaktif)' : '' }}
            </option>
        @endforeach
    </x-form.select>

    {{-- Outlet --}}
    @if(auth()->user()->isStaff())
        {{-- Untuk Staff: Terkunci otomatis ke outlet miliknya --}}
        <div>
            <label class="form-label">Outlet</label>
            <input
                type="text"
                class="form-input bg-neutral-100 text-neutral-muted cursor-not-allowed"
                value="{{ auth()->user()->outlet->name ?? '-' }}"
                disabled
            />
            <input type="hidden" name="outlet_id" value="{{ auth()->user()->outlet_id }}" />
            <p class="text-xs text-neutral-muted mt-1">Diproses otomatis untuk outlet tempat Anda ditugaskan.</p>
        </div>
    @else
        {{-- Untuk Admin/Viewer: Dropdown outlet pilihan --}}
        <x-form.select
            label="Outlet Toko"
            name="outlet_id"
            placeholder="-- Pilih Outlet --"
            required
        >
            @foreach($outlets as $outletOption)
                <option value="{{ $outletOption->id }}" {{ old('outlet_id', $transaction->outlet_id ?? '') == $outletOption->id ? 'selected' : '' }}>
                    {{ $outletOption->name }} {{ !$outletOption->is_active ? '(Nonaktif)' : '' }}
                </option>
            @endforeach
        </x-form.select>
    @endif

    {{-- Nominal (Amount) dengan Format Ribuan Otomatis --}}
    <div>
        <label for="amount_display" class="form-label">
            Jumlah Nominal (Rupiah) <span class="text-danger-600">*</span>
        </label>

        <div class="relative">
            <input
                type="text"
                id="amount_display"
                x-model="formattedAmount"
                x-on:input="formatCurrency($event.target.value)"
                placeholder="Rp 0"
                required
                class="form-input text-lg font-bold text-neutral-text @error('amount') border-danger focus:border-danger focus:ring-danger @enderror"
            />
            <input type="hidden" name="amount" x-model="rawAmount" />
        </div>

        @error('amount')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    {{-- Atas Nama --}}
    <x-form.input
        label="Atas Nama (Penyetor / Pihak Terkait)"
        name="payer_name"
        :value="$transaction->payer_name ?? ''"
        placeholder="Contoh: Toko Outlet 1, Budi (Kasir), dll."
        required
    />

    {{-- Keterangan --}}
    <x-form.textarea
        label="Keterangan Rincian (Opsional)"
        name="description"
        :value="$transaction->description ?? ''"
        placeholder="Tambahkan catatan rincian transaksi jika diperlukan..."
        rows="3"
    />

    {{-- Upload Bukti Gambar --}}
    <div class="pt-2">
        <x-image-upload
            name="proof_image"
            label="Upload Bukti Transaksi (Gambar Opsional)"
            :existingUrl="$transaction->proof_image_url ?? null"
        />
    </div>
</div>

<section id="tanya-jawab" class="bg-ice py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <h2 class="text-3xl font-extrabold tracking-tight text-navy sm:text-4xl">Tanya jawab</h2>

        <div class="mt-8 space-y-3">
            @foreach ([
                ['Apa itu kWh?', 'kWh (kilowatt-jam) adalah satuan pemakaian listrik, angka yang tertulis di meteran PLN. Satu kWh sama dengan alat 1.000 watt yang menyala selama satu jam, atau lampu 10 watt yang menyala 100 jam.'],
                ['Bagaimana WattWise menghitung tagihan?', 'Daya alat (watt) dikali jam menyala per hari, dikali jumlah hari dalam sebulan, lalu dikalikan tarif per kWh golongan listrik Anda. Hasil semua alat dijumlahkan.'],
                ['Saya tidak tahu daya alat saya. Bagaimana?', 'Lihat stiker atau label di belakang atau bawah alat, biasanya tertulis angka dengan huruf W atau Watt. Kalau tidak ketemu, pakai saja angka bawaan di kalkulator ini.'],
                ['Seberapa tepat perkiraannya?', 'Ini perkiraan, karena alat seperti AC dan kulkas tidak selalu bekerja penuh. Setelah daftar, catat angka meteran PLN Anda dan WattWise akan menunjukkan selisih antara perkiraan dan pemakaian nyata.'],
                ['Apa itu sistem pakar?', 'Program yang berisi aturan dari pengetahuan para ahli, misalnya "AC yang menyala 8 jam lebih sebaiknya disetel 24–25°C". WattWise mencocokkan data Anda dengan aturan-aturan itu dan menjelaskan alasan setiap saran.'],
                ['Apakah bisa untuk listrik token (prabayar)?', 'Bisa. Perkiraan pemakaian tetap sama. Saat mencatat meteran, Anda bisa mengisi jumlah token yang dibeli sebagai pengganti tagihan.'],
            ] as [$question, $answer])
                <details class="group card p-0">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold text-navy select-none">
                        {{ $question }}
                        <x-icon name="plus" class="size-5 shrink-0 text-electric transition-transform group-open:rotate-45" />
                    </summary>
                    <p class="px-5 pb-5 leading-relaxed text-slate-600">{{ $answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
